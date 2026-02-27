./vendor/bin/phpcs src
./vendor/bin/phpcbf src

vendor/bin/phpstan analyse src

./vendor/bin/psalm --no-cache


1. де є запис по дб як воно має бути?
2. в реквесті як отримувати дані з пут делейт патч
3. валідатор, як отримувати параметри правильно
4. в індесі як ловити і відправляти помилки правильно
5.


фільтр специфікаці патерн
агрегат


-----rsa------
-----rsa------

mkdir -p storage/keys

openssl genpkey -algorithm RSA -out storage/keys/private.pem -pkeyopt rsa_keygen_bits:4096
openssl rsa -pubout -in storage/keys/private.pem -out storage/keys/public.pem


-----права----

chmod 600 storage/keys/private.pem
chmod 644 storage/keys/public.pem

ле так не робе тому 777

-----права----

-----rsa------
-----rsa------

----бібліотека-----

composer require firebase/php-jwt

----бібліотека-----


----додати таблицю----


CREATE TABLE token_blacklist (
    jti VARCHAR(64) PRIMARY KEY,
    expires_at INT NOT NULL
);


----додати таблицю----


---сервіс-----

app/src/Security/Services/JwtService.php

Можливі винятки:
    ExpiredException
    SignatureInvalidException
    BeforeValidException

---сервіс-----