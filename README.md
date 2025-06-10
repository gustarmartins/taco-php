taco-php (to-do)/
│
├── composer.json                # for autoloading (PSR-4) and future dependencies
├── config/
│   └── database.php             # configuration (e.g., path to SQLite file)
│
├── data/
│   └── taco_preload.sql         # the seed file for initial import
│
├── src/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   └── FoodController.php   # e.g., search alimento, view details
│   │   └── DietController.php   # future: manage diets
│   ├── Models/
│   │   └── BaseModel.php        # base class for PDO access
│   │   └── Alimento.php         # model for the alimento table
│   │   └── Diet.php             # future diet model
│   ├── Views/
│   │   ├── layout.php           # main layout template
│   │   ├── home.php
│   │   ├── alimento/
│   │   │   ├── list.php
│   │   │   └── detail.php
│   │   └── diet/
│   │       ├── index.php
│   │       └── edit.php
│   ├── Helpers/
│   │   └── Router.php           # a simple router or use a micro-router library
│   │   └── View.php             # helper to render templates
│   └── bootstrap.php            # sets up autoload, DB initialization, etc.
│
├── public/
│   ├── index.php                # front controller (all requests go here)
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── .htaccess                # for Apache rewrite to funnel to index.php
│
└── logs/
    └── app.log                  # for logging errors
