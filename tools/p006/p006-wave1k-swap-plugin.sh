#!/usr/bin/env bash
set -euo pipefail

: "${WPE_TEST_WORDPRESS_DIR:?missing WPE_TEST_WORDPRESS_DIR}"
: "${WPE_P006_SWAP_PLUGIN:?missing WPE_P006_SWAP_PLUGIN}"
: "${WPE_P006_SWAP_TARGET:?missing WPE_P006_SWAP_TARGET}"

wp_dir="${WPE_TEST_WORDPRESS_DIR}"
plugins_dir="${wp_dir}/wp-content/plugins"
stage_root="${wp_dir}/wp-content/p006-wave1k-packages"

case "${WPE_P006_SWAP_PLUGIN}" in
  free)
    plugin_slug="wpessential"
    main_file="wpessential.php"
    case "${WPE_P006_SWAP_TARGET}" in
      f0|f1a|f1b) ;;
      *) echo "Unsupported Free swap target: ${WPE_P006_SWAP_TARGET}" >&2; exit 1 ;;
    esac
    ;;
  pro)
    plugin_slug="wpessential-pro"
    main_file="wpessential-pro.php"
    case "${WPE_P006_SWAP_TARGET}" in
      p0|p1) ;;
      *) echo "Unsupported Pro swap target: ${WPE_P006_SWAP_TARGET}" >&2; exit 1 ;;
    esac
    ;;
  *)
    echo "Unsupported swap plugin: ${WPE_P006_SWAP_PLUGIN}" >&2
    exit 1
    ;;
esac

target_dir="${stage_root}/${WPE_P006_SWAP_TARGET}/${plugin_slug}"
link_path="${plugins_dir}/${plugin_slug}"
next_link="${plugins_dir}/.${plugin_slug}.p006-wave1k-next"

test -d "${target_dir}"
test -f "${target_dir}/${main_file}"
test -L "${link_path}"

rm -f "${next_link}"
ln -s "../p006-wave1k-packages/${WPE_P006_SWAP_TARGET}/${plugin_slug}" "${next_link}"
test -L "${next_link}"
test -f "${next_link}/${main_file}"

mv -Tf "${next_link}" "${link_path}"

test -L "${link_path}"
test -f "${link_path}/${main_file}"
resolved="$(readlink -f "${link_path}")"
expected="$(readlink -f "${target_dir}")"
test "${resolved}" = "${expected}"

printf '%s\n' "${resolved}"
