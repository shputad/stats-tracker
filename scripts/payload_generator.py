# payload_generator.py

import sys
import os
from payload_builder import generate_payload_text

def main():
    if len(sys.argv) < 4:
        print("Usage: payload_generator.py <payload_url> <payload_name> <campaign_id>")
        sys.exit(1)

    payload_url = sys.argv[1]
    payload_name = sys.argv[2]
    campaign_id = sys.argv[3]

    try:
        path = generate_payload_text(payload_url, payload_name, campaign_id)
        print(path)
    except Exception as e:
        print(f"Error: {str(e)}")
        sys.exit(2)

if __name__ == "__main__":
    main()
