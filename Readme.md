
# Hot Reactor

Hot Reactor is Hot Reload mechanism for applications. It is built with OpenSwoole development in mind, but it reloads specific commands based on the file changes.

You can install it via composer install or download the project and arrange the `.env` file with the proper settings.

## Setup

There are 2 ways to run the Hot Reactor:

### Option 1: run other folders

To run services located in other directories:

Check the `.env` file for the settings you need to update. The following are the steps to follow:

- Step 1: update the `PHP_BIN` environment setting, so it points to the PHP binary file.
- Step 2: update the `OBJECT_PROCESS_NAME` environment setting, to customize your object process name. This **MUST** be the same as the process name in the service you want to Hot Reload.
- Step 3: update the `WORKING_DIR` environment setting, to point to the directory where the service is located.
- Step 4: update the `COMMAND` environment setting, to point to the command you want to run.
- Step 5: update the `OBJECTS` environment setting, to point to the files you want to watch for changes.

Hot Reactor comes with a command line tool to run the Hot Reactor. You can run the following command to start the Hot Reactor:

```bash
php index.php -w="/path/to/your/service" -c="your-command" -o="your-objects"
```

> As in the example, you can also overwrite the `WORKING_DIR`, `COMMAND` and `OBJECTS` settings via command options.

**Extra Steps**

- Update the `MAIN_PROCESS_NAME` environment setting, to customzie your main process name.

## Option 2: install hot reloaded via composer

To install Hot Reactor via composer, you can run the following command:

```bash
composer require lotharthesavior/hot-reactor
```

After installing the package, you can run the following command to start the Hot Reactor:

```bash
vendor/bin/hot-reactor -w="/path/to/your/service" -c="your-command" -o="your-objects"
```
