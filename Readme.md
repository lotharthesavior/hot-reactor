
# Hot Reactor

Hot Reactor is Hot Reload mechanism for applications. It is built with OpenSwoole development in mind, but it reloads specific commands based on the file changes.

You can install it via composer install or download the project and arrange the `.env` file with the proper settings.

## Setup

There are 2 ways to run the Hot Reactor:

### Option 1: run other folders

To run services located in other directories:

Check the `.env` file for the settings you need to update. The following are the steps to follow:

- Step 1: update the `WORKING_DIR` environment setting, to point to the directory where the service is located. `default: /var/www/html/`
- Step 2: update the `COMMAND` environment setting, to point to the command you want to run. `default: /usr/bin/php server.php`
- Step 3: update the `OBJECTS` environment setting, to point to the files you want to watch for changes. `default: /var/www/html/|/var/www/html/features/`
- Step 4: update the `MAIN_PROCESS_NAME` environment setting, to customzie your main process name. `default: hot-reactor`
- Step 5: update `FILE_EXTENSIONS` environment setting, to specify the file extensions to watch for changes. `default: php|env`

Hot Reactor comes with a command line tool to run the Hot Reactor. You can run the following command to start the Hot Reactor:

```bash
php index.php -w="/path/to/your/service" -c="your-command" -o="your-objects"
```

> As in the example, you can also overwrite the `WORKING_DIR`, `COMMAND` and `OBJECTS` settings via command options.

## Option 2: install hot reloaded via composer

To install Hot Reactor via composer, you can run the following command:

```bash
composer require lotharthesavior/hot-reactor
```

After installing the package, you can run the following command to start the Hot Reactor:

```bash
vendor/bin/hot-reactor -w "/path/to/your/service" -c "your-command" -o "your-objects"
```

---

> **Important**: There is a bug related to when you want to restart an app running sub processes with OpenSwoole\Process. Then, you might need to wait for a SIGKILL at the end of the file so the app restarts gracefully. You do that by adding the following code:
> 
> ```php
> Co::run(function () {
>     System::waitSignal(SIGKILL, -1);
> });
> ```

---

## Contributors

We welcome contributions from everyone! If you're interested in helping improve Hot Reactor, please follow these steps:

1. Fork the repository on GitHub.
2. Make your changes in a branch named for the feature or fix you're working on.
3. Submit a pull request through GitHub for review.

Please make sure your contributions adhere to the following guidelines:

- Code contributions should follow the coding style already established in the project.
- Include comments in your code where necessary to explain complex or non-obvious parts.
- Update the README.md if your changes require it.
- Ensure that your code does not introduce any new bugs or security vulnerabilities.

Thank you for considering contributing to Hot Reactor!

## License

Hot Reactor is open-source software licensed under the MIT license. See the [LICENSE](LICENSE) file for more details.
