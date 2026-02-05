web: php artisan serve --host=0.0.0.0 --port=$PORT
worker: php artisan queue:work --queue=ml-sync,default --timeout=3600 --sleep=3 --tries=3