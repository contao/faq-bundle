Contao 4 FAQ bundle
===================

[![](https://img.shields.io/travis/contao/faq-bundle/master.svg?style=flat-square)](https://travis-ci.org/contao/faq-bundle/)
[![](https://img.shields.io/coveralls/contao/faq-bundle/master.svg?style=flat-square)](https://coveralls.io/github/contao/faq-bundle)
[![](https://img.shields.io/packagist/v/contao/faq-bundle.svg?style=flat-square)](https://packagist.org/packages/contao/faq-bundle)
[![](https://img.shields.io/packagist/dt/contao/faq-bundle.svg?style=flat-square)](https://packagist.org/packages/contao/faq-bundle)

Contao is an Open Source PHP Content Management System for people who want a
professional website that is easy to maintain. Visit the [project website][1]
for more information.

The faq bundle adds FAQ functionality to Contao 4.


Installation
------------

Run the following command in your project directory:

```bash
php composer.phar require contao/faq-bundle "^4.4"
```


Activation
-------------

Adjust to your `app/AppKernel.php` file:

```php
// app/AppKernel.php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Contao\FaqBundle\ContaoFaqBundle(),
        ];
    }
}
```


License
-------

Contao is licensed under the terms of the LGPLv3.


Getting support
---------------

Visit the [support page][2] to learn about the available support options.


[1]: https://contao.org
[2]: https://contao.org/en/support.html
