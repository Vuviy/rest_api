./vendor/bin/phpcs src
./vendor/bin/phpcbf src

vendor/bin/phpstan analyse src

./vendor/bin/psalm --no-cache


1. де є запиз по дб як воно має бути?
2. в реквесті як отримувати дані з пут делейт патч
3. валідатор, як отримувати параметри правильно
4. в індесі як лопити і відправляти помилки правильно
5.