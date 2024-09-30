FROM phpswoole/swoole:php8.3-alpine

WORKDIR /fd

COPY FlashDetector /fd/FlashDetector
COPY FDWebServer/swoole /fd/FDWebServer/swoole
COPY Scripts/env.php /fd/Scripts/env.php
COPY Scripts/ws.php /fd/Scripts/ws.php
COPY sf /fd/sf
COPY sfloader.php /fd/sfloader.php

EXPOSE 8080

CMD ["php", "Scripts/ws.php", "-a", "0.0.0.0", "-p", "8080", "-s"]
