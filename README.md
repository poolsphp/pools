<p align="center">
    <img src="https://raw.githubusercontent.com/pools-php/pools/master/docs/banner.png" height="300" alt="Pools PHP">
    <p align="center">
        <a href="https://github.com/poolsphp/pools/actions"><img alt="GitHub Workflow Status (master)" src="https://github.com/poolsphp/pools/actions/workflows/tests.yml/badge.svg"></a>
        <a href="https://packagist.org/packages/poolsphp/pools"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/poolsphp/pools"></a>
        <a href="https://packagist.org/packages/poolsphp/pools"><img alt="Latest Version" src="https://img.shields.io/packagist/v/poolsphp/pools"></a>
        <a href="https://packagist.org/packages/poolsphp/pools"><img alt="License" src="https://img.shields.io/packagist/l/poolsphp/pools"></a>
    </p>
</p>

---

# Pools PHP

Pools is a CLI tool that helps you install and configure modern PHP development tools for your projects with a single command.

> **Requires [PHP 8.3+](https://php.net/releases/)**

## What Pools Does

Pools v0.0.3 provides a seamless way to install and configure popular PHP development tools:

- 🧹 **Pint**: PHP code style fixer based on PHP-CS-Fixer
- ⚗️ **PHPStan**: Static analysis tool for finding bugs before they reach production
- ✅ **Pest**: An elegant PHP testing framework with a focus on simplicity
- 🔄 **Rector**: Automated refactoring tool to improve your PHP code

Each tool is installed with sensible default configurations, saving you time and ensuring consistent code quality across your projects.

## Installation

Install Pools globally using Composer:

```bash
composer global require poolsphp/pools
```

Make sure to add Composer's global bin directory to your PATH to run Pools as a shell command.

### For Bash/Zsh users (Linux/macOS):

Add the following line to your `~/.bashrc`, `~/.zshrc`, or `~/.bash_profile`:

```bash
export PATH="$PATH:$HOME/.composer/vendor/bin"
```

Then reload your shell configuration:

```bash
source ~/.bashrc  # or ~/.zshrc or ~/.bash_profile
```

### For Windows users:

Add the Composer's global vendor bin directory to your PATH environment variable:
```
%USERPROFILE%\AppData\Roaming\Composer\vendor\bin
```

## Usage

Navigate to your PHP project's root directory and run:

```bash
pools install
```

You'll be prompted to select which tools you want to install:

- PHPStan
- Pest
- Pint
- Rector

### Using specific tools

After installation, you can use the tools through Composer scripts in your project:

```bash
# Run PHP code style fixes
composer lint

# Run automated refactors
composer refacto

# Run static analysis
composer test:types

# Run unit tests with Pest
composer test:unit

# Run the entire test suite
composer test
```

### CLI Options

Install all tools:
```bash
pools install --all
```

Install specific tools:
```bash
pools install --phpstan --pest
pools install --pint --rector
```

Set PHPStan level:
```bash
pools install --phpstan --type-check-level=8
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
