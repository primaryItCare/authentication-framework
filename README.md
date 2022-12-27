 
=> Now open config/app.php file and add service provider and aliase.
'providers' => [
	....
	authwrap\Userform\UserformServiceProvider::class,
],
=> Now that we've installed the package, we'll need to publish the database migration and config file: 
php artisan vendor:publish --provider="authwrap\Userform\UserformServiceProvider"

=> We can now run the migrations to create the new tables in our database:
php artisan migrate