# Ollama para POLL-IA

El chatbot de la app movil consume el backend Laravel en:

```text
POST /api/v1/chatbot/message
```

Laravel decide el proveedor desde `.env`. Para usar Ollama:

```env
CHATBOT_PROVIDER=ollama
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MODEL=llama3.1:8b
OLLAMA_TIMEOUT=60
OLLAMA_TEMPERATURE=0.4
OLLAMA_NUM_PREDICT=350
```

## Local Windows

1. Instala Ollama desde `https://ollama.com`.
2. Descarga el modelo:

```powershell
ollama pull llama3.1:8b
```

3. Verifica que Ollama este activo:

```powershell
ollama list
```

4. Levanta Laravel y prueba:

```powershell
php artisan serve
```

```text
GET http://127.0.0.1:8000/api/v1/chatbot/status
```

Debe responder `ok: true` y `model_installed: true`.

## Docker / Coolify

Si Laravel corre dentro de un contenedor, `127.0.0.1` apunta al contenedor, no al host. Usa una URL alcanzable desde el contenedor:

```env
OLLAMA_URL=http://host.docker.internal:11434
```

En servidores Linux puede ser mejor publicar Ollama como servicio interno de la misma red y usar su nombre:

```env
OLLAMA_URL=http://ollama:11434
```

## Prueba manual

```bash
curl -X POST http://127.0.0.1:8000/api/v1/chatbot/message \
  -H "Content-Type: application/json" \
  -d "{\"message\":\"Que pollos tienen disponibles?\",\"guest_session\":\"demo-session-001\"}"
```

La app movil no necesita hablar directo con Ollama. Solo debe apuntar a la API Laravel correcta en `assets/runtime_config.json`.
