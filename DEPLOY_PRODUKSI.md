# Panduan Update Server Produksi — Fix WebSocket Reverb Panel Realtime

> **Masalah:** Panel realtime berhenti menerima update otomatis setelah 2+ jam tanpa refresh.  
> **Penyebab utama:** Browser menolak koneksi `ws://` (HTTP) dari halaman `https://` → Mixed Content Policy.  
> **Bukti:** Console browser menampilkan `WebSocket connection to 'wss://localhost:8080/...' failed`.

---

## Arsitektur Server Produksi (Hasil Analisis Aktual)

```
Browser (HTTPS)
    │
    │  https://ep.edutec.my.id  (port 443, SSL)
    ▼
Nginx (port 443, SSL termination)
    │
    │  proxy_pass https://127.0.0.1:8290  (semua traffic)
    ▼
Server di port 8290 (OpenLiteSpeed / LiteSpeed / backend lain)
    │
    │  PHP-FPM → Laravel app
    │
    │  WebSocket /app/* → ??? (saat ini BELUM ada route ke Reverb)
    ▼
Laravel Reverb (port 8080, berjalan via Supervisor)
```

**Temuan penting:** Semua traffic diforward ke port 8290, termasuk request WebSocket.
Port 8290 harus meneruskan path `/app/*` ke Reverb di port 8080.

---

## Apakah Port 8080 Konflik dengan Laravel?

**TIDAK.** Dua proses berbeda, dua port berbeda:

| Proses | Port | Keterangan |
|--------|------|------------|
| Nginx (publik) | 80 / 443 | SSL termination, reverse proxy |
| Backend (OLS/backend) | 8290 | Web server internal |
| Laravel Reverb | 8080 | WebSocket server, internal only |

---

## File yang Harus Diupdate di Server Produksi

---

### 1. Update `.env` Produksi

File: `/www/wwwroot/ep.edutec.my.id/sis/.env` (atau sesuai path project Laravel)

Hapus **semua** baris Reverb lama (ada 3 blok duplikat), ganti dengan satu blok ini:

```ini
# ── Reverb WebSocket ─────────────────────────────────────────────────────────
# REVERB_HOST/PORT/SCHEME = nilai yang dilihat browser (via Nginx)
# Browser connect ke wss://ep.edutec.my.id/app/... (port 443, HTTPS)
REVERB_APP_ID=534041
REVERB_APP_KEY=bqaxtm3hwaxxp4kzfbxt
REVERB_APP_SECRET=t9j6mkapmzfkxiibfnba
REVERB_HOST=ep.edutec.my.id
REVERB_PORT=443
REVERB_SCHEME=https

# Server Reverb listen di dalam server (tidak perlu diubah)
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# ── Vite env untuk frontend (di-compile ke JS saat npm run build) ────────────
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

---

### 2. Update Nginx Config — Tambah Location untuk WebSocket Reverb

File: `/www/server/panel/vhost/nginx/ep.edutec.my.id.conf`

Backup sudah ada di `ep.edutec.my.id.conf.backup` ✓

Tambahkan blok `location /app` dan `location /apps` **SEBELUM** `location /`:

```nginx
server
{
    listen 80;
    listen 443 ssl http2;
    server_name ep.edutec.my.id;

    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/ep.edutec.my.id/;

    include /www/server/panel/vhost/nginx/extension/ep.edutec.my.id/*.conf;
    include /www/server/panel/vhost/nginx/well-known/ep.edutec.my.id.conf;

    #SSL-START
    #error_page 404/404.html;
    ssl_certificate    /www/server/panel/vhost/cert/ep.edutec.my.id/fullchain.pem;
    ssl_certificate_key    /www/server/panel/vhost/cert/ep.edutec.my.id/privkey.pem;
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_ciphers EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_tickets on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    add_header Strict-Transport-Security "max-age=31536000";
    error_page 497 https://$host$request_uri;
    #SSL-END

    # ══════════════════════════════════════════════════════════════════════════
    # REVERB WEBSOCKET PROXY — harus SEBELUM location / agar tidak tertimpa
    # Browser connect ke wss://ep.edutec.my.id/app/KEY?...
    # Nginx forward langsung ke Reverb di port 8080 (plain WebSocket, no TLS)
    # ══════════════════════════════════════════════════════════════════════════
    location /app {
        proxy_pass             http://127.0.0.1:8080;
        proxy_http_version     1.1;
        proxy_set_header       Upgrade $http_upgrade;
        proxy_set_header       Connection "Upgrade";
        proxy_set_header       Host $host;
        proxy_set_header       X-Real-IP $remote_addr;
        proxy_set_header       X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header       X-Forwarded-Proto https;
        proxy_read_timeout     3600s;
        proxy_send_timeout     3600s;
        proxy_connect_timeout  60s;
    }

    location /apps {
        proxy_pass             http://127.0.0.1:8080;
        proxy_http_version     1.1;
        proxy_set_header       Upgrade $http_upgrade;
        proxy_set_header       Connection "Upgrade";
        proxy_set_header       Host $host;
        proxy_set_header       X-Real-IP $remote_addr;
        proxy_set_header       X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header       X-Forwarded-Proto https;
        proxy_read_timeout     3600s;
        proxy_send_timeout     3600s;
        proxy_connect_timeout  60s;
    }
    # ══════════════════════════════════════════════════════════════════════════

    # Semua traffic lain tetap ke backend port 8290 (tidak berubah)
    location / {
        proxy_pass https://127.0.0.1:8290;
        proxy_ssl_server_name on;
        proxy_ssl_name $host;
        proxy_ssl_session_reuse off;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header REMOTE-HOST $remote_addr;
        proxy_set_header SERVER_PROTOCOL $server_protocol;
        proxy_set_header HTTPS $https;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $connection_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header REMOTE_PORT $remote_port;
        add_header Cache-Control no-cache;
    }

    # Forbidden files or directories
    location ~ ^/(\.user.ini|\.htaccess|\.git|\.env|\.svn|\.project|LICENSE|README.md)
    {
        return 404;
    }

    location ~ \.well-known{
        allow all;
        root /www/wwwroot/ep.edutec.my.id/;
        try_files $uri =404;
    }

    if ( $uri ~ "^/\.well-known/.*\.(php|jsp|py|js|css|lua|ts|go|zip|tar\.gz|rar|7z|sql|bak)$" ) {
        return 403;
    }

    access_log /www/wwwlogs/ep.edutec.my.id.log;
    error_log  /www/wwwlogs/ep.edutec.my.id.error.log;
}
```

> **Mengapa `location /app` harus SEBELUM `location /`?**  
> Nginx mencocokkan lokasi berdasarkan urutan prefix. Jika `location /` ada duluan,
> semua request `/app/...` akan masuk ke port 8290 dan tidak pernah sampai ke Reverb.

> **Mengapa proxy ke `http://127.0.0.1:8080` (bukan https)?**  
> SSL sudah ditangani Nginx di layer paling luar. Komunikasi internal server ke Reverb
> boleh plain HTTP — lebih ringan dan tidak perlu certificate internal.

---

### 3. Cek `0.websocket.conf` (file yang sudah ada)

```bash
sudo cat /www/server/panel/vhost/nginx/0.websocket.conf
```

File ini kemungkinan berisi definisi variabel `$connection_upgrade` yang dipakai di `location /`.
Pastikan isinya ada map seperti ini (biasanya sudah ada di BoletaPanel/AAPanel):

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
```

Jika belum ada, tambahkan di `/www/server/nginx/conf/nginx.conf` dalam blok `http {}`.

---

### 4. Supervisor Config — Sudah Benar, Tidak Perlu Diubah

Dari screenshot, config Supervisor sudah tepat:
```
command=/usr/bin/php /var/www/root/ep.edutec.my.id/sis/artisan reverb:start --host=0.0.0.0 --port=8080 --no-interaction
autostart=true
autorestart=true
```

Hanya pastikan `startretries=3` ada di config agar Reverb restart otomatis jika crash.

---

### 5. `config/reverb.php` — Deploy via Git Pull

Sudah diupdate di lokal:
- `ping_interval`: 60 → **30 detik**
- `activity_timeout`: 30 → **60 detik**

---

## Urutan Perintah Deploy di Server

```bash
# ── 1. Masuk direktori project ───────────────────────────────────────────────
cd /www/wwwroot/ep.edutec.my.id/sis

# ── 2. Pull kode terbaru ─────────────────────────────────────────────────────
git pull origin main

# ── 3. Edit .env (hapus 3 blok Reverb lama, ganti 1 blok baru) ──────────────
nano .env
# atau: vi .env

# ── 4. Rebuild asset frontend (WAJIB setelah ubah .env) ──────────────────────
npm run build

# ── 5. Bersihkan cache Laravel ───────────────────────────────────────────────
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
php artisan optimize

# ── 6. Restart Reverb ────────────────────────────────────────────────────────
supervisorctl restart Spv_presen1

# ── 7. Update Nginx config (edit file, lalu test & reload) ───────────────────
sudo nano /www/server/panel/vhost/nginx/ep.edutec.my.id.conf
# (paste config lengkap dari panduan di atas)

sudo nginx -t
# Output harus: "syntax is ok" dan "test is successful"

sudo systemctl reload nginx
# atau: sudo /etc/init.d/nginx reload

# ── 8. Verifikasi Reverb berjalan ────────────────────────────────────────────
supervisorctl status Spv_presen1
ss -tlnp | grep 8080
```

---

## Verifikasi Setelah Deploy

### A. Cek Reverb berjalan di port 8080:
```bash
ss -tlnp | grep 8080
# Harus: *:8080  LISTEN
```

### B. Cek WebSocket bisa diakses dari luar:
```bash
# Dari server sendiri, test curl upgrade request:
curl -i -N \
  -H "Connection: Upgrade" \
  -H "Upgrade: websocket" \
  -H "Host: ep.edutec.my.id" \
  -H "Origin: https://ep.edutec.my.id" \
  http://127.0.0.1:8080/app/bqaxtm3hwaxxp4kzfbxt
# Harus dapat response: HTTP/1.1 101 Switching Protocols
```

### C. Cek dari browser — DevTools → Network → filter `WS`:
- URL: `wss://ep.edutec.my.id/app/bqaxtm3hwaxxp4kzfbxt?...` ✓
- Status: `101 Switching Protocols` ✓
- **Tidak ada lagi** `wss://localhost:8080/...` ✓

### D. Cek console browser tidak ada error:
```
✓ [RTP] Echo: connected
✓ [RTP] Channel subscribed ✓
✗ WebSocket connection to 'wss://localhost:8080/...' failed  ← harus hilang
```

---

## Rollback Jika Ada Masalah

```bash
# Kembalikan Nginx config ke backup
sudo cp /www/server/panel/vhost/nginx/ep.edutec.my.id.conf.backup \
        /www/server/panel/vhost/nginx/ep.edutec.my.id.conf

sudo nginx -t && sudo systemctl reload nginx
```

---

## Ringkasan Semua Masalah & Solusi

| # | Masalah | Penyebab | Solusi |
|---|---------|---------|--------|
| 1 | WS connect ke `localhost:8080` | `VITE_REVERB_*` terkunci ke nilai duplikat lama saat build | Bersihkan `.env`, jalankan `npm run build` |
| 2 | Browser tolak koneksi WS | `REVERB_SCHEME=http` di domain HTTPS (Mixed Content) | `REVERB_SCHEME=https`, `REVERB_PORT=443` |
| 3 | WS path `/app/...` masuk ke port 8290, bukan Reverb | Tidak ada `location /app` di Nginx | Tambah `location /app` → proxy ke port 8080 **sebelum** `location /` |
| 4 | WS putus setelah beberapa menit | Nginx default `proxy_read_timeout 60s` | Tambah `proxy_read_timeout 3600s` di location WebSocket |
| 5 | Panel tidak reconnect setelah putus | `MAX_RECONNECT_TRIES=10` lalu berhenti total | Ganti ke `Infinity` + exponential backoff ✓ (sudah fix) |
| 6 | Koneksi mati karena idle NAT/proxy | Tidak ada heartbeat | `pusher:ping` setiap 25 detik ✓ (sudah fix) |
| 7 | Port 8080 vs 8000 konflik? | **TIDAK KONFLIK** | Reverb=8080, Laravel backend=8290, Nginx=443 |
