"""List contract sub-settings that a module's admin UI does not reference.

Static search only: an option counts as present when its last id segment (e.g. `add_new` for
`cpt.labels.add_new`) appears in the module's admin PHP or admin-ui source. Run: python3 tools/audit/ui-option-coverage.py
"""
import glob
import json
import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
# Not meant to have a control: unsafe, owned by another surface, runtime-only evidence, or deferred.
SKIP = {'REJECTED_UNSAFE', 'OUT_OF_SURFACE_REFERENCE', 'RUNTIME_IMPLEMENTATION_EVIDENCE', 'DEFERRED'}

MODULES = {
    'cpt': ['frameworks/Modules/CustomPostTypes'],
    'taxonomy': ['frameworks/Modules/Taxonomies'],
    'relations': ['frameworks/Modules/Relations'],
    'fields': ['frameworks/Modules/Fields'],
    'columns': ['frameworks/Modules/AdminColumns'],
}


def source_ids(key):
    ids = []
    for f in sorted(glob.glob(f'{ROOT}/config/product/option-contract-projections/{key}/*.json')):
        ids += [e['source_id'] for e in json.load(open(f))['entries'] if e['disposition'] not in SKIP]
    if not ids:
        contract = json.load(open(f'{ROOT}/config/product/option-contracts/{key}.json'))
        ids = [e['source_id'] for e in contract.get('source_projection', {}).get('entries', []) if e['disposition'] not in SKIP]
    return ids


def code_text(dirs):
    text = []
    for d in dirs + ['admin-ui/src']:
        for f in glob.glob(f'{ROOT}/{d}/**/*', recursive=True):
            if f.endswith(('.php', '.js', '.jsx', '.ts', '.tsx', '.mjs')):
                text.append(open(f, encoding='utf-8', errors='ignore').read())
    return '\n'.join(text)


def missing(key, dirs):
    text = code_text(dirs)
    out = []
    for sid in source_ids(key):
        token = sid.split('.')[-1]
        if not re.search(r'\b' + re.escape(token) + r'\b', text):
            out.append(sid)
    return out


if __name__ == '__main__':
    report = {k: missing(k, d) for k, d in MODULES.items()}
    if '--json' in sys.argv:
        print(json.dumps(report, indent=1))
    else:
        for k, ids in report.items():
            print(f'{k}: {len(ids)} of {len(source_ids(k))} sub-settings not referenced by the admin UI')
