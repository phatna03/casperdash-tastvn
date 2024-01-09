+ Setup project
  + Source code
    + npm install
    + composer install
    + npm run dev
    + php artisan serve --port=[your_port]
  + Database
    + php artisan migrate
    + php artisan passport:install --uuids
    + php artisan passport:keys (first-time-when-deploy-to-server)
  + User Admin
    + admin@casperdash.io
    + pass= 123456
+ Libraries
  + passport
    + https://laravel.com/docs/10.x/passport
    + composer require laravel/passport
  + image
    + https://github.com/Intervention/image
    + composer require intervention/image
  + pdf
    + https://github.com/dompdf/dompdf
    + composer require dompdf/dompdf
  + mail
    + https://github.com/snowfire/Beautymail
    + composer require snowfire/beautymail
  + aws
