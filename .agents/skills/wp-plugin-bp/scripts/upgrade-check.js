#!/usr/bin/env node
'use strict';

/**
 * upgrade-check.js
 *
 * Self-contained upgrade comparison report for WordPress plugins based on the
 * boilerplate. Run this AFTER cloning the upstream reference and running
 * `plugin-replace.php` against that clone with the TARGET plugin's identity.
 * Because the reference has already been rewritten to the target identity,
 * file paths, namespaces, and text domains line up, so a path-based diff is
 * meaningful.
 *
 * What it does:
 *   - Compares every upgradable "area" described in references/upgrade.md.
 *   - Diffs config/source files, and produces version-mismatch tables for the
 *     composer.json / package.json dependency and script sections.
 *   - Tolerates missing files and whole missing areas (very old plugins) by
 *     classifying them instead of crashing.
 *   - Prints a Markdown report to stdout whose final section tells the AI to fan
 *     out one subagent per area that needs review.
 *
 * Usage:
 *   node upgrade-check.js [--target <dir>] [--ref <dir>] [--max-diff-lines <n>]
 *                         [--external <area-keys>]
 *
 *   --target           Plugin being upgraded. Default: current directory.
 *   --ref              Path to the rewritten upstream reference clone. Accepts
 *                      any absolute or relative path, so the clone does not have
 *                      to live in the default location.
 *                      Default: <target>/tmp/plugin-ref
 *   --max-diff-lines   Truncate each file diff to N lines. Default: 160.
 *   --external         Comma-separated area keys that are managed OUTSIDE the
 *                      plugin (e.g. a monorepo root owns CI workflows or ignore
 *                      files). Those areas are reported as `external`: upstream
 *                      files are listed to verify at that location instead of
 *                      being flagged "missing → adopt". Valid keys: php-qa,
 *                      js-bundling, github-actions, loader, main-class, bootstrap,
 *                      i18n, abilities, file-control.
 *   --help             Print this help.
 *
 * Exit codes: 0 = report produced (regardless of findings), 2 = fatal setup
 * error (e.g. reference directory missing).
 */

const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

function main() {
	let opts;
	try {
		opts = parseArgs(process.argv.slice(2));
	} catch (err) {
		process.stderr.write(String(err.message || err) + '\n');
		process.exit(2);
	}

	if (opts.help) {
		printHelp();
		process.exit(0);
	}

	// Validate args before touching the filesystem.
	const externalKeys = new Set(opts.external || []);
	// Skipped areas (e.g. agents) can't be externally-managed — exclude them.
	const validKeys = new Set(AREAS.filter((a) => !a.skip).map((a) => a.key));
	const unknown = [...externalKeys].filter((k) => !validKeys.has(k));
	if (unknown.length) {
		fatal(
			`Unknown --external area key(s): ${unknown.join(', ')}\n` +
				`Valid keys: ${[...validKeys].join(', ')}`
		);
	}

	const target = path.resolve(opts.target || process.cwd());
	const ref = path.resolve(opts.ref || path.join(target, 'tmp', 'plugin-ref'));

	if (!isDir(target)) {
		fatal(`Target plugin directory not found: ${target}`);
	}
	if (!isDir(ref)) {
		fatal(
			`Reference directory not found: ${ref}\n` +
				'Clone the upstream reference and run plugin-replace.php against it first:\n' +
				'  git clone --depth 1 <upstream-url> tmp/plugin-ref\n' +
				'  php tmp/plugin-ref/.agents/skills/wp-plugin-bp/scripts/plugin-replace.php \\\n' +
				'    --path tmp/plugin-ref --plugin-name "..." --plugin-namespace "..." \\\n' +
				'    --plugin-text-domain "..." --cleanup-setup'
		);
	}

	const ctx = {
		target,
		ref,
		maxDiffLines: opts.maxDiffLines,
		gitAvailable: detectGit(),
		externalKeys,
	};

	const identity = detectIdentity(target);
	const areas = AREAS.map((area) => evaluateArea(area, ctx));
	const report = {
		generatedAt: new Date().toISOString(),
		target,
		ref,
		identity,
		gitAvailable: ctx.gitAvailable,
		areas,
	};

	process.stdout.write(renderMarkdown(report) + '\n');
	process.exit(0);
}

/* ------------------------------------------------------------------ *
 * Area definitions (mirror references/upgrade.md "Upgradable parts") *
 * ------------------------------------------------------------------ */

const AREAS = [
	{
		key: 'php-qa',
		title: 'PHP & QA configuration',
		confirmation: false,
		refs: ['references/doc-i18n.mdx', 'references/doc-bundling.mdx'],
		manifests: [{ rel: 'composer.json', kind: 'composer' }],
		files: ['phpcs.xml', 'phpstan.neon', 'phpunit.xml.dist'],
		hint: 'Sync dependency versions, QA config, and composer scripts. Adapt namespace/text-domain in any copied config.',
	},
	{
		key: 'js-bundling',
		title: 'JS & bundling',
		confirmation: false,
		refs: ['references/doc-bundling.mdx', 'references/doc-wp-env.mdx', 'references/doc-create-blocks.mdx'],
		manifests: [{ rel: 'package.json', kind: 'npm' }],
		files: ['webpack.config.js', '.wp-env.json'],
		hint: 'Sync npm deps, build/lint scripts, and webpack entry points. Watch for new wp-scripts flags (e.g. --experimental-modules).',
	},
	{
		key: 'github-actions',
		title: 'GitHub Actions workflows',
		confirmation: true,
		refs: ['references/doc-github-actions.mdx'],
		dirs: ['.github/workflows'],
		hint: 'Compare each workflow (setup, analysis, tests, deploy, release, translation). Confirm before replacing CI/CD.',
	},
	{
		key: 'loader',
		title: 'Loader (src/Loader.php)',
		confirmation: true,
		files: ['src/Loader.php'],
		hint: 'Review shortcode, WP-CLI, and Abilities API registration helpers. Confirm before restructuring Loader.',
	},
	{
		key: 'main-class',
		title: 'Main plugin class',
		confirmation: true,
		discover: discoverMainClass,
		hint: 'Review asset enqueueing via entry points, block registration, and loader registration patterns. PLUGIN_VERSION stays the single source of truth.',
	},
	{
		key: 'bootstrap',
		title: 'Bootstrap file (@wordpress-plugin)',
		confirmation: true,
		discover: discoverBootstrap,
		hint: 'Check the <text_domain_snake>_loaded action on plugins_loaded (priority 0, passes version) and the <NAMESPACE_UPPER>_VERSION constant mirroring PLUGIN_VERSION. Do not add a second version literal.',
	},
	{
		key: 'i18n',
		title: 'i18n workflow',
		confirmation: false,
		refs: ['references/doc-i18n.mdx'],
		custom: checkI18n,
		hint: 'Verify i18n:extract / i18n:compile composer scripts and generated language-file expectations.',
	},
	{
		key: 'abilities',
		title: 'Abilities API (src/Abilities/)',
		confirmation: true,
		refs: ['references/doc-abilities.mdx'],
		dirs: ['src/Abilities'],
		hint: 'Compare ability interfaces and add_ability() usage. A missing directory means the plugin predates the Abilities API; confirm before introducing it.',
	},
	{
		key: 'file-control',
		title: 'File-control files',
		confirmation: false,
		files: ['.distignore', '.gitignore'],
		hint: 'Merge new ignore patterns; keep plugin-specific entries.',
	},
	{
		key: 'agents',
		title: 'Agent configuration (.agents / skills)',
		skip: true,
		hint: 'Do NOT diff or copy .agents/skills as boilerplate source. Use `npx skills update -p` to refresh installed skills, and ask before removing local-only skills.',
	},
];

/* ----------------------- *
 * Area discovery helpers  *
 * ----------------------- */

// The main class is the src/*.php file that declares `const PLUGIN_VERSION`.
function discoverMainClass(ctx) {
	const find = (root) =>
		listFiles(root, 'src')
			.filter((rel) => rel.endsWith('.php') && rel.split('/').length === 2)
			.filter((rel) => /PLUGIN_VERSION/.test(safeRead(path.join(root, rel)) || ''));
	return unionRel(find(ctx.target), find(ctx.ref));
}

// The bootstrap file is the root-level *.php containing the @wordpress-plugin marker.
function discoverBootstrap(ctx) {
	const find = (root) =>
		listFiles(root, '.')
			.filter((rel) => rel.endsWith('.php') && !rel.includes('/'))
			.filter((rel) => /@wordpress-plugin/.test(safeRead(path.join(root, rel)) || ''));
	return unionRel(find(ctx.target), find(ctx.ref));
}

/* ----------------------- *
 * Area evaluation         *
 * ----------------------- */

function evaluateArea(area, ctx) {
	const result = {
		key: area.key,
		title: area.title,
		confirmation: !!area.confirmation,
		refs: area.refs || [],
		hint: area.hint || '',
		files: [],
		manifests: [],
		notes: [],
		status: 'clean',
	};
	// `status` is overwritten on every path below; the initializer just documents the shape.

	if (area.skip) {
		result.status = 'skipped';
		if (area.hint) result.notes.push(area.hint);
		return result;
	}

	// Resolve the file list: explicit files, directory contents, and discovery.
	// A single union at the end dedups and sorts everything.
	const relFiles = [];
	if (area.files) relFiles.push(...area.files);
	if (area.dirs) {
		for (const dir of area.dirs) {
			relFiles.push(...listFiles(ctx.target, dir), ...listFiles(ctx.ref, dir));
		}
	}
	if (area.discover) relFiles.push(...area.discover(ctx));

	for (const rel of unionRel(relFiles)) {
		result.files.push(compareFile(rel, ctx));
	}

	for (const manifest of area.manifests || []) {
		result.manifests.push(compareManifest(manifest, ctx));
	}

	if (area.custom) {
		const custom = area.custom(ctx);
		if (custom.notes) result.notes.push(...custom.notes);
		if (custom.files) result.files.push(...custom.files);
		if (custom.manifests) result.manifests.push(...custom.manifests);
		if (typeof custom.applicable === 'boolean') result.customApplicable = custom.applicable;
		if (typeof custom.needsReview === 'boolean') result.customNeedsReview = custom.needsReview;
	}

	// Areas the operator declared as managed outside the plugin (e.g. a monorepo
	// root owns CI workflows or ignore files) are not adopted locally. We keep the
	// computed file comparisons so genuine local overrides still surface, but the
	// status becomes `external` so missing upstream files don't read as "adopt".
	if (ctx.externalKeys.has(area.key)) {
		result.status = 'external';
		return result;
	}

	result.status = rollupStatus(result);
	return result;
}

function rollupStatus(result) {
	const fileStatuses = result.files.map((f) => f.status);
	const manifestHasMismatch = result.manifests.some(
		(m) => m.error || (m.mismatches && m.mismatches.length > 0)
	);

	const needsReview =
		manifestHasMismatch ||
		result.customNeedsReview === true ||
		fileStatuses.some((s) => s === 'differs' || s === 'missing-in-target' || s === 'error');

	if (needsReview) return 'review';

	// Nothing to review. Distinguish "area absent entirely" from "clean & present".
	const anythingPresent =
		result.customApplicable === true ||
		result.files.some((f) => f.status !== 'absent') ||
		result.manifests.some((m) => m.targetExists || m.refExists);
	if (!anythingPresent) return 'not-applicable';
	return 'clean';
}

/* ----------------------- *
 * File comparison         *
 * ----------------------- */

function compareFile(rel, ctx) {
	const tPath = path.join(ctx.target, rel);
	const rPath = path.join(ctx.ref, rel);
	const tExists = fs.existsSync(tPath);
	const rExists = fs.existsSync(rPath);

	if (!tExists && !rExists) return { rel, status: 'absent' };
	if (tExists && !rExists) return { rel, status: 'only-in-target' };
	if (!tExists && rExists) return { rel, status: 'missing-in-target' };

	let tBuf, rBuf;
	try {
		tBuf = fs.readFileSync(tPath);
		rBuf = fs.readFileSync(rPath);
	} catch (err) {
		return { rel, status: 'error', error: String(err.message || err) };
	}
	if (tBuf.equals(rBuf)) return { rel, status: 'identical' };

	const diff = makeDiff(tPath, rPath, ctx);
	return { rel, status: 'differs', diff };
}

// Returns { stat: {added, removed}, patch, truncated } using git when available.
function makeDiff(tPath, rPath, ctx) {
	if (!ctx.gitAvailable) {
		const t = (safeRead(tPath) || '').split('\n').length;
		const r = (safeRead(rPath) || '').split('\n').length;
		return {
			stat: null,
			patch: `(git unavailable) files differ — your plugin ${t} lines, reference ${r} lines.`,
			truncated: false,
		};
	}

	// A single invocation yields the numstat header line followed by the patch body.
	const out = git(['diff', '--no-index', '--numstat', '-p', '--', tPath, rPath]).stdout || '';

	let stat = null;
	const parts = out.split('\n', 1)[0].split('\t');
	const added = parseInt(parts[0], 10);
	const removed = parseInt(parts[1], 10);
	if (!Number.isNaN(added) && !Number.isNaN(removed)) stat = { added, removed };

	const patchStart = out.indexOf('diff --git');
	const lines = (patchStart >= 0 ? out.slice(patchStart) : out).split('\n');
	const truncated = lines.length > ctx.maxDiffLines;
	const patch = lines.slice(0, ctx.maxDiffLines).join('\n');
	return { stat, patch, truncated };
}

/* ----------------------- *
 * Manifest comparison     *
 * ----------------------- */

const MANIFEST_SECTIONS = {
	composer: { deps: ['require', 'require-dev'], scripts: 'scripts' },
	npm: { deps: ['dependencies', 'devDependencies'], scripts: 'scripts' },
};

function compareManifest(manifest, ctx) {
	const { rel, kind } = manifest;
	const out = {
		rel,
		kind,
		targetExists: fs.existsSync(path.join(ctx.target, rel)),
		refExists: fs.existsSync(path.join(ctx.ref, rel)),
		mismatches: [],
		scriptDiffs: [],
		error: null,
	};

	if (!out.refExists && !out.targetExists) return out;
	if (!out.refExists) {
		out.error = 'Reference manifest missing — cannot compare.';
		return out;
	}
	if (!out.targetExists) {
		out.error = 'Target manifest missing — upstream ships this file; consider adopting it.';
		return out;
	}

	const t = readJson(path.join(ctx.target, rel));
	const r = readJson(path.join(ctx.ref, rel));
	if (!t.ok || !r.ok) {
		out.error = `Could not parse JSON (${!t.ok ? 'target' : 'reference'}): ${
			!t.ok ? t.error : r.error
		}`;
		return out;
	}

	const sections = MANIFEST_SECTIONS[kind];
	for (const section of sections.deps) {
		const tdeps = t.data[section] || {};
		const rdeps = r.data[section] || {};
		for (const pkg of unionRel(Object.keys(tdeps), Object.keys(rdeps))) {
			const tv = tdeps[pkg];
			const rv = rdeps[pkg];
			if (tv === rv) continue;
			out.mismatches.push({ section, pkg, target: tv || null, ref: rv || null, kind: classifyKind(tv, rv) });
		}
	}

	// Compare the scripts object (string equality per key).
	const tScripts = t.data[sections.scripts] || {};
	const rScripts = r.data[sections.scripts] || {};
	for (const name of unionRel(Object.keys(tScripts), Object.keys(rScripts))) {
		const tv = scriptToString(tScripts[name]);
		const rv = scriptToString(rScripts[name]);
		if (tv === rv) continue;
		out.scriptDiffs.push({ name, target: tv || null, ref: rv || null, kind: classifyKind(tv, rv) });
	}

	return out;
}

function scriptToString(v) {
	if (v === undefined) return undefined;
	return Array.isArray(v) ? v.join(' && ') : String(v);
}

// Classify a target-vs-reference value pair: only in reference = missing (upstream
// adds it), only in target = extra (plugin-specific), present in both = changed.
function classifyKind(tv, rv) {
	if (tv === undefined) return 'missing';
	if (rv === undefined) return 'extra';
	return 'changed';
}

/* ----------------------- *
 * Custom area: i18n       *
 * ----------------------- */

function checkI18n(ctx) {
	const notes = [];
	let needsReview = false;
	let applicable = false;
	const t = readJson(path.join(ctx.target, 'composer.json'));
	const r = readJson(path.join(ctx.ref, 'composer.json'));

	const tScripts = (t.ok && t.data.scripts) || {};
	const rScripts = (r.ok && r.data.scripts) || {};
	for (const name of ['i18n:extract', 'i18n:compile']) {
		const has = !!tScripts[name];
		const refHas = !!rScripts[name];
		if (has || refHas) applicable = true;
		if (!has && refHas) {
			notes.push(`Missing composer script \`${name}\` — upstream provides it.`);
			needsReview = true;
		} else if (has && refHas && scriptToString(tScripts[name]) !== scriptToString(rScripts[name])) {
			notes.push(`Composer script \`${name}\` differs from upstream.`);
			needsReview = true;
		} else if (!has && !refHas) {
			notes.push(`Neither plugin defines \`${name}\` (i18n may not apply).`);
		}
	}

	const tLang = isDir(path.join(ctx.target, 'languages'));
	if (!tLang) {
		notes.push('No `languages/` directory in target — i18n area may not apply yet.');
	} else {
		applicable = true;
		const pot = listFiles(ctx.target, 'languages').filter((f) => f.endsWith('.pot'));
		if (pot.length === 0) notes.push('`languages/` exists but contains no `.pot` template.');
		else notes.push(`Found translation template: ${pot.join(', ')}.`);
	}

	return { notes, needsReview, applicable };
}

/* ----------------------- *
 * Markdown rendering      *
 * ----------------------- */

function renderMarkdown(report) {
	const L = [];
	L.push('# Upgrade Comparison Report');
	L.push('');
	L.push(`- Generated: ${report.generatedAt}`);
	L.push(`- Target plugin: \`${report.target}\``);
	L.push(`- Upstream reference: \`${report.ref}\``);
	if (report.identity.name || report.identity.namespace || report.identity.textDomain) {
		L.push(
			`- Detected identity: name="${report.identity.name || '?'}", namespace="${
				report.identity.namespace || '?'
			}", text-domain="${report.identity.textDomain || '?'}"`
		);
	}
	if (!report.gitAvailable) L.push('- ⚠️ `git` not found — diffs are summarized as line counts only.');
	L.push('');

	// Summary table.
	L.push('## Summary');
	L.push('');
	L.push('| Area | Status | Notes |');
	L.push('| --- | --- | --- |');
	for (const a of report.areas) {
		L.push(`| ${a.title} | ${statusBadge(a.status)} | ${areaOneLiner(a)} |`);
	}
	L.push('');

	// Per-area detail.
	for (const a of report.areas) {
		if (a.status === 'skipped') {
			L.push(`## ${a.title} — skipped`);
			L.push('');
			for (const n of a.notes) L.push(`> ${n}`);
			L.push('');
			continue;
		}
		if (a.status === 'external') {
			L.push(...renderExternalArea(a));
			continue;
		}
		L.push(`## ${a.title} — ${statusBadge(a.status)}`);
		L.push('');
		if (a.confirmation) L.push('> ⚠️ Confirmation required before applying changes in this area.');
		if (a.refs.length) L.push(`> Reference docs to consult: ${a.refs.map((r) => `\`${r}\``).join(', ')}`);
		L.push('');

		for (const n of a.notes) L.push(`- ${n}`);
		if (a.notes.length) L.push('');

		// Manifests (dependency + script mismatch tables).
		for (const m of a.manifests) {
			L.push(...renderManifest(m));
		}

		// Files.
		const interesting = a.files.filter((f) => f.status !== 'absent' && f.status !== 'identical');
		const quiet = a.files.filter((f) => f.status === 'identical');
		if (interesting.length) {
			L.push('### Files');
			L.push('');
			for (const f of interesting) L.push(...renderFile(f));
		}
		if (quiet.length) {
			L.push(`_Identical: ${quiet.map((f) => `\`${f.rel}\``).join(', ')}_`);
			L.push('');
		}
		if (!interesting.length && !quiet.length && !a.manifests.length && !a.notes.length) {
			L.push('_No relevant files found in either plugin._');
			L.push('');
		}
	}

	// Next steps for the AI.
	L.push(...renderNextSteps(report));
	return L.join('\n');
}

function renderManifest(m) {
	const L = [];
	L.push(`### \`${m.rel}\` (${m.kind})`);
	L.push('');
	if (m.error) {
		L.push(`- ⚠️ ${m.error}`);
		L.push('');
		return L;
	}
	if (!m.mismatches.length && !m.scriptDiffs.length) {
		L.push('- Dependencies and scripts match the reference.');
		L.push('');
		return L;
	}
	if (m.mismatches.length) {
		L.push('Dependency / version mismatches:');
		L.push('');
		L.push('| Section | Package | Your plugin | Upstream | Action |');
		L.push('| --- | --- | --- | --- | --- |');
		for (const x of m.mismatches) {
			L.push(
				`| ${x.section} | \`${x.pkg}\` | ${x.target || '—'} | ${x.ref || '—'} | ${mismatchAction(
					x.kind
				)} |`
			);
		}
		L.push('');
	}
	if (m.scriptDiffs.length) {
		L.push('Script mismatches:');
		L.push('');
		L.push('| Script | Your plugin | Upstream | Action |');
		L.push('| --- | --- | --- | --- |');
		for (const x of m.scriptDiffs) {
			L.push(
				`| \`${x.name}\` | ${codeCell(x.target)} | ${codeCell(x.ref)} | ${mismatchAction(x.kind)} |`
			);
		}
		L.push('');
	}
	return L;
}

function renderFile(f) {
	const L = [];
	if (f.status === 'missing-in-target') {
		L.push(`- \`${f.rel}\` — **missing in your plugin** (upstream ships it; consider adopting).`);
		L.push('');
		return L;
	}
	if (f.status === 'only-in-target') {
		L.push(`- \`${f.rel}\` — only in your plugin (plugin-specific; leave as-is unless deprecated).`);
		L.push('');
		return L;
	}
	if (f.status === 'error') {
		L.push(`- \`${f.rel}\` — ⚠️ ${f.error}`);
		L.push('');
		return L;
	}
	// differs
	const stat = f.diff && f.diff.stat ? ` (+${f.diff.stat.added}/-${f.diff.stat.removed})` : '';
	L.push(`- \`${f.rel}\` — **differs**${stat}`);
	L.push('');
	if (f.diff && f.diff.patch) {
		L.push('```diff');
		L.push(f.diff.patch);
		if (f.diff.truncated) L.push('... (diff truncated; open the files to see the rest)');
		L.push('```');
		L.push('');
	}
	return L;
}

// An externally-managed area (declared via --external): list the upstream files
// to verify at the monorepo root, plus any genuine local copies that drifted.
function renderExternalArea(a) {
	const L = [];
	L.push(`## ${a.title} — ${statusBadge(a.status)}`);
	L.push('');
	L.push(
		'> Marked external via `--external`: managed outside the plugin (e.g. a monorepo root), not per-plugin. ' +
			'Verify against that location; do not adopt plugin-local copies.'
	);
	if (a.refs.length) L.push(`> Reference docs to consult: ${a.refs.map((r) => `\`${r}\``).join(', ')}`);
	L.push('');

	for (const n of a.notes) L.push(`- ${n}`);
	if (a.notes.length) L.push('');

	const upstream = a.files.filter((f) => f.status === 'missing-in-target');
	const localDiffs = a.files.filter((f) => f.status === 'differs' || f.status === 'only-in-target');
	const identical = a.files.filter((f) => f.status === 'identical');

	if (upstream.length) {
		L.push('Upstream provides these — confirm the external location has current equivalents (they may be renamed):');
		L.push('');
		for (const f of upstream) L.push(`- \`${f.rel}\``);
		L.push('');
	}
	if (localDiffs.length) {
		L.push('Plugin-local copies exist — reconcile only if they are genuine per-plugin overrides:');
		L.push('');
		for (const f of localDiffs) L.push(...renderFile(f));
	}
	for (const m of a.manifests) L.push(...renderManifest(m));
	if (identical.length) {
		L.push(`_Identical local copies: ${identical.map((f) => `\`${f.rel}\``).join(', ')}_`);
		L.push('');
	}
	return L;
}

function renderNextSteps(report) {
	const L = [];
	L.push('---');
	L.push('');
	L.push('## How to act on this report (instructions for the AI)');
	L.push('');
	const review = report.areas.filter((a) => a.status === 'review');
	const external = report.areas.filter((a) => a.status === 'external');

	if (!review.length && !external.length) {
		L.push(
			'No areas need review — your plugin matches the upstream reference. Run the verification steps in `references/upgrade.md` and remove `tmp/plugin-ref`.'
		);
		L.push('');
		return L;
	}

	if (review.length) {
		L.push(
			`Spawn ONE subagent per area marked **review** below, in parallel (single message, multiple Agent calls). ` +
				`Each subagent owns exactly one area: it reads the diffs/mismatches in this report, reads the listed reference docs, ` +
				`then proposes (and, once confirmed where required, applies) scoped patches — never wholesale file replacement. ` +
				`After copying upstream code, adapt namespace, text domain, paths, and plugin-specific behavior.`
		);
		L.push('');
	} else {
		L.push('No areas require local changes.');
		L.push('');
	}

	L.push('Hard rules:');
	L.push('- Do NOT diff or copy `.agents/skills`; refresh with `npx skills update -p` instead.');
	L.push('- Confirm with the user before changing areas flagged "Confirmation required".');
	L.push('- Areas marked **not-applicable** are absent in both plugins — skip them.');
	if (external.length) {
		L.push(
			'- Areas marked **external** are managed outside the plugin (monorepo root). Do NOT add plugin-local copies; verify the external location instead.'
		);
	}
	L.push('');

	if (review.length) {
		L.push(`Dispatch ${review.length} subagent(s):`);
		L.push('');
		for (const a of review) {
			L.push(`### Subagent: ${a.title}${a.confirmation ? ' (confirmation required)' : ''}`);
			const docs = a.refs.length ? a.refs.join(', ') : 'none';
			L.push(
				`Compare and reconcile the "${a.title}" area between this plugin and \`${report.ref}\`. ` +
					`${a.hint} Reference docs to read first: ${docs}. ` +
					`Use the mismatches and diffs for this area from the upgrade report as your starting point.`
			);
			L.push('');
		}
	}

	if (external.length) {
		L.push('### Externally-managed areas (monorepo)');
		L.push(
			`${external.length} area(s) were marked \`--external\`: ${external.map((a) => a.title).join(', ')}. ` +
				`Do NOT add plugin-local copies of these. Instead verify the monorepo root (or wherever the area is owned) ships current equivalents — ` +
				`often renamed with a per-repo prefix (e.g. \`<prefix>-deploy.yml\` for \`deploy.yml\`) — and reconcile only genuine plugin-local overrides flagged in each area's section above.`
		);
		L.push('');
	}

	return L;
}

/* ----------------------- *
 * Small render helpers    *
 * ----------------------- */

function statusBadge(status) {
	switch (status) {
		case 'review':
			return '🔴 review';
		case 'clean':
			return '🟢 clean';
		case 'not-applicable':
			return '⚪ not-applicable';
		case 'skipped':
			return '⏭️ skipped';
		case 'external':
			return '🔵 external';
		default:
			return status;
	}
}

function mismatchAction(kind) {
	if (kind === 'missing') return 'add (upstream-only)';
	if (kind === 'extra') return 'keep (plugin-specific)';
	return 'update version';
}

function areaOneLiner(a) {
	if (a.status === 'skipped') return 'Not compared (agent config).';
	if (a.status === 'not-applicable') return 'Absent in both plugins.';
	if (a.status === 'clean') return 'Matches upstream.';
	if (a.status === 'external') {
		const upstream = a.files.filter((f) => f.status === 'missing-in-target').length;
		const local = a.files.filter((f) => f.status === 'differs' || f.status === 'only-in-target').length;
		const bits = ['managed externally; verify at monorepo root'];
		if (upstream) bits.push(`${upstream} upstream file(s) to confirm`);
		if (local) bits.push(`${local} local copy/copies to reconcile`);
		return bits.join('; ');
	}
	const bits = [];
	const diffs = a.files.filter((f) => f.status === 'differs').length;
	const missing = a.files.filter((f) => f.status === 'missing-in-target').length;
	const errors = a.files.filter((f) => f.status === 'error').length;
	const depMismatch = a.manifests.reduce((n, m) => n + (m.mismatches ? m.mismatches.length : 0), 0);
	const scriptMismatch = a.manifests.reduce((n, m) => n + (m.scriptDiffs ? m.scriptDiffs.length : 0), 0);
	if (diffs) bits.push(`${diffs} file diff(s)`);
	if (missing) bits.push(`${missing} missing upstream file(s)`);
	if (depMismatch) bits.push(`${depMismatch} dep mismatch(es)`);
	if (scriptMismatch) bits.push(`${scriptMismatch} script mismatch(es)`);
	if (errors) bits.push(`${errors} error(s)`);
	return bits.join(', ') || 'Needs review.';
}

function codeCell(v) {
	if (!v) return '—';
	const s = String(v).replace(/\|/g, '\\|');
	return '`' + (s.length > 80 ? s.slice(0, 77) + '...' : s) + '`';
}

/* ----------------------- *
 * Identity detection      *
 * ----------------------- */

function detectIdentity(root) {
	const identity = { name: null, namespace: null, textDomain: null };
	// Bootstrap header for name + text domain.
	const bootstraps = listFiles(root, '.').filter(
		(rel) => rel.endsWith('.php') && !rel.includes('/')
	);
	for (const rel of bootstraps) {
		const content = safeRead(path.join(root, rel)) || '';
		if (!/@wordpress-plugin/.test(content)) continue;
		const name = content.match(/Plugin Name:\s*(.+)/i);
		const td = content.match(/Text Domain:\s*(.+)/i);
		if (name) identity.name = name[1].trim();
		if (td) identity.textDomain = td[1].trim();
		break;
	}
	// PSR-4 namespace from composer.json.
	const composer = readJson(path.join(root, 'composer.json'));
	if (composer.ok) {
		const psr4 = composer.data.autoload && composer.data.autoload['psr-4'];
		if (psr4) {
			const ns = Object.keys(psr4).find((k) => psr4[k] === 'src/' || psr4[k] === 'src');
			if (ns) identity.namespace = ns.replace(/\\+$/, '');
		}
	}
	return identity;
}

/* ----------------------- *
 * Filesystem + git utils  *
 * ----------------------- */

function listFiles(root, sub) {
	const base = path.join(root, sub);
	if (!isDir(base)) return [];
	const out = [];
	const walk = (dir) => {
		let entries;
		try {
			entries = fs.readdirSync(dir, { withFileTypes: true });
		} catch (err) {
			return;
		}
		for (const e of entries) {
			const abs = path.join(dir, e.name);
			if (e.isDirectory()) walk(abs);
			else if (e.isFile()) out.push(path.relative(root, abs).split(path.sep).join('/'));
		}
	};
	walk(base);
	return out;
}

function unionRel(a, b) {
	return Array.from(new Set([...(a || []), ...(b || [])])).sort();
}

function isDir(p) {
	try {
		return fs.statSync(p).isDirectory();
	} catch (err) {
		return false;
	}
}

function safeRead(p) {
	try {
		return fs.readFileSync(p, 'utf8');
	} catch (err) {
		return null;
	}
}

function readJson(p) {
	try {
		return { ok: true, data: JSON.parse(fs.readFileSync(p, 'utf8')) };
	} catch (err) {
		return { ok: false, error: String(err.message || err) };
	}
}

function detectGit() {
	const r = spawnSync('git', ['--version'], { encoding: 'utf8' });
	return !r.error && r.status === 0;
}

function git(args) {
	return spawnSync('git', args, { encoding: 'utf8', maxBuffer: 32 * 1024 * 1024 });
}

/* ----------------------- *
 * CLI                     *
 * ----------------------- */

function parseArgs(argv) {
	const opts = { maxDiffLines: 160 };
	for (let i = 0; i < argv.length; i++) {
		const arg = argv[i];
		const next = () => {
			const v = argv[++i];
			if (v === undefined) throw new Error(`Missing value for ${arg}`);
			return v;
		};
		switch (arg) {
			case '--target':
				opts.target = next();
				break;
			case '--ref':
				opts.ref = next();
				break;
			case '--max-diff-lines':
				opts.maxDiffLines = parseInt(next(), 10) || 160;
				break;
			case '--external':
				opts.external = next()
					.split(',')
					.map((s) => s.trim())
					.filter(Boolean);
				break;
			case '--help':
			case '-h':
				opts.help = true;
				break;
			default:
				throw new Error(`Unknown option: ${arg}`);
		}
	}
	return opts;
}

function printHelp() {
	process.stdout.write(
		[
			'upgrade-check.js — upgrade comparison report for boilerplate-based WordPress plugins',
			'',
			'Run AFTER cloning the upstream reference and running plugin-replace.php against it',
			'with the target plugin identity.',
			'',
			'Usage:',
			'  node upgrade-check.js [--target <dir>] [--ref <dir>] [--max-diff-lines <n>] [--external <keys>]',
			'',
			'  --target           Plugin being upgraded (default: cwd)',
			'  --ref              Path to the rewritten upstream reference clone;',
			'                     any absolute or relative path (default: <target>/tmp/plugin-ref)',
			'  --max-diff-lines   Truncate each file diff to N lines (default: 160)',
			'  --external         Comma-separated area keys managed outside the plugin (monorepo root):',
			'                     reported as `external` (verify there, do not adopt locally).',
			'                     Keys: php-qa, js-bundling, github-actions, loader, main-class,',
			'                     bootstrap, i18n, abilities, file-control',
			'  --help             Show this help',
			'',
		].join('\n')
	);
}

function fatal(msg) {
	process.stderr.write(msg + '\n');
	process.exit(2);
}

main();
