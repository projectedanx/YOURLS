import yaml
import sys
import re

def extract_pdt_block(filename):
    with open(filename, 'r') as f:
        content = f.read()

    match = re.search(r'```yaml\s*PDT_SPECIFICATION_BLOCK\s*\n(.*?)\n```', content, re.DOTALL)
    if match:
        return match.group(1)
    return None

def validate_pdt(yaml_content):
    try:
        docs = list(yaml.safe_load_all(yaml_content))
        if not docs:
            print("No YAML documents found.")
            return False

        data = {}
        for doc in docs:
            if isinstance(doc, dict):
                data.update(doc)
    except yaml.YAMLError as e:
        print(f"YAML Parsing Error: {e}")
        return False

    required_keys = ['DRP_ID', 'PART_NAME', 'DATUMS', 'FEATURES']
    for key in required_keys:
        if key not in data:
            print(f"Missing required key: {key}")
            return False

    print("Metrological Conformance test passed: AGENTS.md adheres to PDT specification.")
    return True

if __name__ == "__main__":
    pdt_block = extract_pdt_block('AGENTS.md')
    if pdt_block:
        if validate_pdt(pdt_block):
            sys.exit(0)
        else:
            sys.exit(1)
    else:
        print("PDT_SPECIFICATION_BLOCK not found in AGENTS.md")
        sys.exit(1)
