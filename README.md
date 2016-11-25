# Dravencms Packager

This is a simple Packager module for dravencms

## Instalation

The best way to install dravencms/packager is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/packager:@dev
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	packager: Dravencms\Packager\DI\PackagerExtension
```
