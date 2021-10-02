# !/bin/bash

usage()
{
    echo "Usage:
            -h  show this help text
            -f  filename in snake_case e.g.: 'demo_plugin'. Will be used for assets
            -n  namespace in Pascal_Snake_Case e.g.: 'Demo_Plugin'. Will be used for the namespace and the main plugin class"
}

no_args="true"
while getopts ':hf:n:' option; do
  case "$option" in
    h) usage
      exit
      ;;
    f) filename=$OPTARG
      filename_minus=${filename/_/-}
      ;;
     n) namespace=$OPTARG
      ;;
    :) printf "missing argument for -%s\n" "$OPTARG" >&2
      usage;
      exit 1
      ;;
   \?) printf "illegal option: -%s\n" "$OPTARG" >&2
      usage;
      exit 1
      ;;
  esac
  no_args="false"
done

# Check if parameters set
if [ "$no_args" == "true" ]
then
  echo "No Parameters passed"
  usage;
  exit 1;
elif [[ (-z "$filename") || (-z "$namespace") ]]
then
  echo "You need to pass the -f and the -n parameters"
  usage;
  exit 1;
fi

shift $((OPTIND - 1))

# Special Renamings
sed -i "s/DEMO_PLUGIN/$namespace^/g" demo-plugin.php;
sed -i "s/demo_plugin/$namespace,,/g" demo-plugin.php;
sed -i "s/demo_plugin/$namespace,,/g" includes/i18n.php;

# Rename files with "-" separation
for filename in $(find . -name 'demo-plugin*'); do echo mv \"$filename\" \"${filename//demo-plugin/$filename_minus}\"; done | /bin/bash
# Rename files with "_" separation -> most likely php classes
for filename in $(find . -name 'demo_plugin*'); do echo mv \"$filename\" \"${filename//demo-plugin/$filename}\"; done | /bin/bash
# Rename Main Class
for filename in $(find ./includes -name 'Demo_Plugin*'); do echo mv \"$filename\" \"${filename//Demo_Plugin/$namespace}\"; done | /bin/bash

echo "Successfully renamed all demo files."
echo ---

# Replace Namespace in all Files
find ./ -type f -name '*.php' -exec sed -i "s/Demo_Plugin/$namespace/g" {} \;
sed -i "s/Demo_Plugin/$namespace/g" composer.json;

echo "Successfully renamed all namespaces."
echo ---


# Install Dependencies
composer update

echo ---
echo "Dependencies installed. Make final tests"
echo ---
composer run static-analyse
