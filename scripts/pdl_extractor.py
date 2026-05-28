#!/usr/bin/env python3
"""
PDL Extractor (Isomorphic Bridge Tool - PAT-001)
Extracts and validates Progressive Disclosure Level (PDL) decorators from Markdown files.
Demonstrates structural mapping between semantic concepts and computational representation.
"""

import sys
import re
import argparse
from typing import List, Dict

def extract_pdl_decorators(filepath: str) -> List[Dict[str, str]]:
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
    except FileNotFoundError:
        print(f"Error: File not found - {filepath}", file=sys.stderr)
        sys.exit(1)

    # Match patterns like +++ContextLock(anchor="DOMAIN_PAIR", refresh_interval=2048)
    # or just +++EpistemicEscrow
    pattern = re.compile(r'\+{3}([A-Za-z0-9_]+)(?:\((.*?)\))?')

    decorators = []
    for match in pattern.finditer(content):
        name = match.group(1)
        args_str = match.group(2)

        args = {}
        if args_str:
            # Simple naive parse of key=value
            kv_pairs = re.findall(r'([a-zA-Z0-9_]+)\s*=\s*("[^"]*"|\'[^\']*\'|[^,]+)', args_str)
            for k, v in kv_pairs:
                args[k] = v.strip(' "\'')

        decorators.append({
            'decorator': name,
            'args': args
        })

    return decorators

def main():
    parser = argparse.ArgumentParser(description="Extract PDL decorators (+++Name) from a Markdown file.")
    parser.add_argument("file", help="Path to the Markdown file")
    parser.add_argument("--format", choices=["text", "json"], default="text", help="Output format")

    args = parser.parse_args()

    results = extract_pdl_decorators(args.file)

    if args.format == "json":
        import json
        print(json.dumps(results, indent=2))
    else:
        print(f"Extracted {len(results)} PDL decorators from {args.file}:\n")
        for r in results:
            args_formatted = ", ".join(f"{k}={v}" for k, v in r['args'].items())
            if args_formatted:
                print(f"+++{r['decorator']}({args_formatted})")
            else:
                print(f"+++{r['decorator']}")

if __name__ == "__main__":
    main()
