## Compare Databases
## Utility to compare contents of a database table for two Magento 2 instances

## :eye: Installation

1) Run following command to install dependencies: `composer install`

2) Configure SSH and database settings
    - Configuration files lives in: `/config`
    - Each project should have it's own directory. Example: `/config/Project`
    - Inside each project's directory there should be a `settings.yaml` file
      - Structure should look like: `/config/project/settings.yaml` 
      - `/config/settings.yaml.sample` should be used as skeleton

3) Run the following command to init the interactive utility:
```
$ bin/console compare -p <projectname>
```

# Send us your feedback!
:email: sdecicco@hammer.net

## Badges

![](https://img.shields.io/badge/license-MIT-blue.svg)
![](https://img.shields.io/badge/status-stable-green.svg)

