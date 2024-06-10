# Telegram n8n Proxy

A Simple PHP proxy to bypass IPv6 related error in n8n.

```sh
Can’t connect to Telegram - EHOSTUNREACH
```

```sh

connect EHOSTUNREACH IPV6:443 (connect EHOSTUNREACH IPV6:443)
Execution Error.

Error stack:

NodeApiError: connect EHOSTUNREACH IPV6:443
    at Object.apiRequest (/usr/lib/node_modules/n8n/node_modules/n8n-nodes-base/nodes/Telegram/GenericFunctions.ts:179:9)
    at processTicksAndRejections (node:internal/process/task_queues:95:5)
    at Object.execute (/usr/lib/node_modules/n8n/node_modules/n8n-nodes-base/nodes/Telegram/Telegram.node.ts:2003:21)
    at Workflow.runNode (/usr/lib/node_modules/n8n/node_modules/n8n-workflow/src/Workflow.ts:1269:19)
    at /usr/lib/node_modules/n8n/node_modules/n8n-core/src/WorkflowExecute.ts:952:29

```

## n8n Telegram Base URL Proxy

The issue mentioned above arose because the Telegram webhook only resolves IPv4 addresses, while most Linux machines support IPv6. Consequently, the connection to the Telegram webhook failed when attempted via n8n due to the inability to resolve IPv6 addresses.  

For certain reasons, disabling IPv6 entirely is not an option for me. Later on, I came up with the idea of using a proxy to resolve the Telegram API URL.  

- `tgproxy.php` just download and host it on your PHP enabled server
- Goto Credentials > Telegram API > Base URL > Update default URL to  `https://proxysite.com/tgproxy.php?url=https://api.telegram.org`  
- after update the URL you can see the `Connection tested successfully` Message
- Now all Telegram related services workout without `EHOSTUNREACH` Errors

## Proxy for only Sending Messages

- `telegram.php` this proxy for only send messages and other POST related activities > <https://core.telegram.org/bots/api#sendmessage>
- cURL examples

```sh
curl -G 'https://proxysite.com/telegram.php' --data-urlencode 'url=https://api.telegram.org/bot<Your Bot token>/sendMessage?chat_id=<Chat ID>&text=Hello%2C%20World!'
```

```sh
curl -X POST 'https://proxysite.com/telegram.php' -H "Content-Type: application/json" -d '{
  "url": "https://api.telegram.org/bot<Your Bot token>/sendMessage",
  "data": {
    "chat_id": "<Chat ID>",
    "text": "Hello, World!"
  }
}'
```

## LICENSE

MIT
