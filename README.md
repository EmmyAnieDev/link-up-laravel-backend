# Broadcasting Chat Setup

## Things to Remember

### 1. Routes/Channels

-   Define your broadcast channels in `routes/channels.php`.

### 2. MessageEvent

-   Ensure that your `MessageEvent` implements the `ShouldQueue` interface to queue the event.

### 3. Configuration

-   Update the `config/broadcast.php` configuration file as needed.

### 4. Queueing

-   Run the queue worker with the following command:

    ```bash
    php artisan queue:work
    ```

-   Alternatively, you can set the QUEUE_CONNECTION to sync for immediate execution:

    env_file

    QUEUE_CONNECTION=sync
