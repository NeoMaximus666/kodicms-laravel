## KodiCMS based on Laravel PHP Framework

[![Join the chat at https://gitter.im/KodiCMS/kodicms-laravel](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/KodiCMS/kodicms-laravel?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Для установки системы, необходимо:

 * Клонировать репозиторий `git clone git@github.com:KodiCMS/kodicms-laravel.git`
 * Запустить команду `composer install` для загрузки всех необходимых компонентов
 * Выполнить команду `php artisan cms:modules:migrate` для создания таблиц в БД.
 * Выполнить команду `php artisan cms:modules:seed` для заполения тестовыми данными БД
 
---

### Авторизация

Сайт: http://laravel.kodicms.ru/backend

username: **admin@site.com**  
password: **password**

username: **test@test.com**  
password: **password**

---

### Консольные команды

 * `cms:modules:migrate` - создание таблиц в БД
 * `cms:modules:seed` - заполнение таблиц тестовыми данными
 * `cms:generate:translate:js` - генерация JS языковых файлов
 * `cms:generate:locale` - генерация пакета lang файлов для перевода. Файлы будут скопированы в `/resources/lang/packages`

---

### Загрузка сервис-провайдеров и алиасов
Изначально Laravel загружает сервис-провайдеры и алиасы из конфиг файла `config/app.php`, но чтобы отделить системных провайдеров от пользовательских, они были вынесены в отдельные файлы `modules/CMS/providers.php` и `modules/CMS/aliases.php`, пользовательские подключать можно по прежнему через конфиг.

### Структура модуля
 * `config` - конфиги приложения, могут быть перезаписаны из папки `/config/`
  * `permissions.php` - Служит для указания списка прав
  * `sitemap.php` - Служит для указания страниц для меню админ панели
  * `behaviors.php`
 * `Console`
  * `Commands` - расположение файлов консольных компанды
 * `database`
  * `migrations` - файлы миграции, будут запущены по команде `cms:modules:migrate`
  * `seeds`
   * `DatabaseSeeder.php` - если существует, то будет запущен по команде `cms:modules:seed`
 * `Helpers` - вспомогательные классы модуля
 * `Http`
  * `Controllers` - контроллеры модуля
  * `Middleware`
  * `routes.php` - роуты текущего модуля, оборачиваются в неймспейс `KodiCMS\{module}`
 * `Observers` - Наблюдатели для моделей Eloquent
 * `Providers`
  * `ModuleServiceProvider.php` - Сервис провайдер (наследуемый от `KodiCMS\CMS\Providers\ServiceProvider`), если есть, будет запущен в момент инициализации приложения
 * `resources`
  * `js` - JavaScript файлы, в этой папке происходит поиск js файлов по виртуальным путям `/backend/cms/js/{script.js}`
  * `lang` - Файлы переводов для модуля, доступны по ключу названия модуля приведенного в нижний регистр `trans('{module}::file.key')`
  * `views` - Шаблоны модуля, доступны по ключу названия модуля приведенного в нижний регистр `view('{module}::template')`
  * `packages.php` - В данном файле можно подключать свои Assets (Media) пакеты
 * `Services` - Сервисные контейнеры
 * `ModuleContainer.php` - Если данный файл существует, то он будет подключен как системный файл модуля, в котором указаны относительыне пути и действия в момент инициализации. Необходимо наследовать от `KodiCMS\CMS\Loader\ModuleContainer`

---

### Состав модулей
 * CMS
  1. Dashboard
 * Pages
  1. Page
  2. Layout
  3. PagePart
 * Users
  1. User
  2. Role
  3. Permission
 * Widgets
  1. Widget
  2. Blocks
  3. Snippet
 * Filemanager
  1. elFinder
 * Email
  1. Email
  2. Email Templates
  3. Email Types

---

### События

#### Frontend Controller
 * `frontend.requested [string $uri]`
 * `frontend.found [FrontPage $page]`
 * `frontend.not_found [string $uri]`

#### Settings Controller
 * `backend.settings.validate [array $settings]`
 * `backend.settings.save [array $settings]`
 
---

### События в шаблонах

#### pages/create
 * `view.page.create`
 
#### pages/edit
 * `view.page.edit.before [KodiCMS\Pages\Model\Page $page]`
 * `view.page.edit [KodiCMS\Pages\Model\Page $page]`

#### backend/navbar
 * `view.navbar.left`
 * `view.navbar.right.before`
 * `view.navbar.right.after`

#### backend/navigation
 * `view.menu.before`
 * `view.menu.after`
 * `view.navigation.before`
 * `view.navigation.after`

### system/about
 * `view.system.about`

#### auth/login
 * `view.login.form.header`
 * `view.login.form.footer`
 * `view.login.form.after`

### auth/password
 * `view.password.form.footer`
 
### user/profile
 * `view.user.profile.information`

### user/edit
 * `view.user.edit.form.password [KodiCMS\Users\Model\User $user]`
 * `view.user.edit.form.bottom [KodiCMS\Users\Model\User $user]`

### user/create
  * `view.user.create.form.password`
  * `view.user.create.form.bottom`

### page/part
 * `view.page.part.controls`
 * `view.page.part.options`

### system/settings
 * `view.settings.top`
 * `view.settings.bottom`

---

### Регистрация консольных комманд через ServiceProvider
В KodiCMS есть базовый сервисный провайдер, в котором уже реализован метод для регистрации комманд. Для использования необходимо наследовать класс провайдера от `KodiCMS\CMS\Providers\ServiceProvider`
Пример регистрации команды

	public function register()
	{
		$this->registerConsoleCommand('module.seed', '\KodiCMS\Installer\Console\Commands\ModuleSeed');
	}

---
#### Отдельное спасибо команде JetBrains за бесплатно предоставленый ключ для PHPStorm
![PHPStorm](https://www.jetbrains.com/phpstorm/documentation/docs/logo_phpstorm.png)