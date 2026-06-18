# Ollama para POLL-IA

## Alternativa sin VPS, pago ni credenciales

Si no puedes instalar Ollama y tampoco quieres usar cuentas/API keys, configura el chatbot en modo local:

```env
CHATBOT_PROVIDER=local
```

Este modo usa:

- Productos activos de la base de datos.
- Datos de pago configurados en `.env`.
- Horario y contacto de la empresa.
- El archivo `resources/chatbot/knowledge.md`.

No es un modelo generativo como Ollama, pero funciona en cualquier hosting y no necesita servicios externos.

El chatbot de la app movil consume el backend Laravel en:

```text
POST /api/v1/chatbot/message
```

Laravel decide el proveedor desde `.env`. Para usar Ollama:

```env
CHATBOT_PROVIDER=ollama
CHATBOT_ENABLE_OLLAMA=true
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_BASE_URL=
OLLAMA_API_KEY=
OLLAMA_MODEL=llama3.1:8b
OLLAMA_TIMEOUT=60
OLLAMA_TIMEOUT_SECONDS=
OLLAMA_TEMPERATURE=0.4
OLLAMA_NUM_PREDICT=350
OLLAMA_KEEP_ALIVE=10m
```

La web y la app movil no se conectan directo a Ollama. Ambas consumen Laravel:

```text
POST /api/v1/chatbot/message
GET  /api/v1/chatbot/status
```

El widget web de POLL-IA esta integrado en `resources/views/store/layout.blade.php`.

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

## Hosting sin permiso para instalar Ollama

Si tu hosting no permite instalar Ollama, conserva Laravel en ese hosting y ejecuta Ollama en otro lugar:

1. Un VPS economico con Docker o Linux.
2. Una PC/servidor propio encendido.
3. Otro servicio privado que exponga una API compatible con Ollama.

Laravel solo necesita alcanzar esta URL:

```env
OLLAMA_URL=http://IP_O_DOMINIO_PRIVADO:11434
```

Si expones Ollama por un dominio publico, ponlo detras de un proxy con token y configura:

```env
OLLAMA_URL=https://ia.tudominio.com
OLLAMA_API_KEY=UN_TOKEN_LARGO_Y_SECRETO
```

Laravel enviara ese token como `Authorization: Bearer ...`.

## Ollama Cloud

Si tienes una API key de ollama.com para modelos cloud, puedes usar:

```env
CHATBOT_PROVIDER=ollama
CHATBOT_ENABLE_OLLAMA=true
OLLAMA_BASE_URL=https://ollama.com
OLLAMA_API_KEY=TU_API_KEY_DE_OLLAMA
OLLAMA_MODEL=gpt-oss:120b
OLLAMA_TIMEOUT_SECONDS=60
OLLAMA_TEMPERATURE=0.35
OLLAMA_NUM_PREDICT=450
OLLAMA_KEEP_ALIVE=10m
```

El cliente Laravel convertira esa base en llamadas a:

```text
https://ollama.com/api/chat
https://ollama.com/api/tags
```

## Variables para hosting

Configura estas variables en el panel del hosting o Coolify:

```env
CHATBOT_PROVIDER=ollama
CHATBOT_ENABLE_OLLAMA=true
OLLAMA_URL=http://ollama:11434
OLLAMA_BASE_URL=
OLLAMA_API_KEY=
OLLAMA_MODEL=llama3.1:8b
OLLAMA_TIMEOUT=90
OLLAMA_TIMEOUT_SECONDS=
OLLAMA_TEMPERATURE=0.35
OLLAMA_NUM_PREDICT=450
OLLAMA_KEEP_ALIVE=10m

COMPANY_BRAND_NAME="Pollos y Parrillas El Dorado"
COMPANY_SUPPORT_PHONE=999888777
COMPANY_SUPPORT_EMAIL=ventas@tudominio.com
COMPANY_HOURS="11:00 a. m. a 10:00 p. m."
```

Si Ollama esta instalado directamente en el servidor y Laravel esta en Docker, usa normalmente:

```env
OLLAMA_URL=http://host.docker.internal:11434
```

Si Ollama esta en otro servidor privado:

```env
OLLAMA_URL=http://IP_PRIVADA_DEL_SERVIDOR:11434
```

Si Ollama esta en otro servidor con proxy HTTPS protegido:

```env
OLLAMA_URL=https://ia.tudominio.com
OLLAMA_API_KEY=UN_TOKEN_LARGO_Y_SECRETO
```

No expongas Ollama publicamente a internet sin proxy/autenticacion. Laravel debe ser quien reciba las preguntas.

## Checklist de servidor Ollama

1. Instala Ollama en el servidor donde correra el modelo.
2. Descarga el modelo:

```bash
ollama pull llama3.1:8b
```

3. Verifica que el modelo exista:

```bash
ollama list
```

4. Si Laravel esta en otro contenedor/host, permite que Ollama escuche en la red interna:

```bash
OLLAMA_HOST=0.0.0.0:11434 ollama serve
```

En produccion, limita ese puerto con firewall/red privada para que solo Laravel pueda acceder.

## Ejemplo recomendado con VPS externo

En el VPS:

```bash
curl -fsSL https://ollama.com/install.sh | sh
ollama pull llama3.1:8b
OLLAMA_HOST=0.0.0.0:11434 ollama serve
```

Prueba desde el VPS:

```bash
curl http://127.0.0.1:11434/api/tags
```

En el hosting de Laravel, configura:

```env
CHATBOT_PROVIDER=ollama
OLLAMA_URL=http://IP_DEL_VPS:11434
OLLAMA_API_KEY=
OLLAMA_MODEL=llama3.1:8b
OLLAMA_TIMEOUT=90
OLLAMA_TEMPERATURE=0.35
OLLAMA_NUM_PREDICT=450
OLLAMA_KEEP_ALIVE=10m
```

Importante: abre el puerto `11434` solo para la IP de tu hosting Laravel. Si tu hosting no tiene IP fija, usa un proxy HTTPS con token y rellena `OLLAMA_API_KEY`.

## Prueba manual

```bash
curl -X POST http://127.0.0.1:8000/api/v1/chatbot/message \
  -H "Content-Type: application/json" \
  -d "{\"message\":\"Que pollos tienen disponibles?\",\"guest_session\":\"demo-session-001\"}"
```

En hosting:

```bash
curl -X POST https://tu-dominio.com/api/v1/chatbot/message \
  -H "Content-Type: application/json" \
  -d '{"message":"Cuales son sus medios de pago?","guest_session":"web-test-0001"}'
```

La app movil no necesita hablar directo con Ollama. Solo debe apuntar a la API Laravel correcta en `assets/runtime_config.json`.
