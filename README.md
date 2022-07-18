# Demo Distribution

The distribution for demo.neos.io

## Setup & Installation

We use Localbeach from [Flownative](https://beach.flownative.com) for the development.
That leads to the requirements:

* [Docker](https://www.docker.com/)
* [Localbeach](https://www.flownative.com/en/products/localbeach.html)

### Installing the Local Beach CLI

Install Beach CLI by running the following commands (using Homebrew):

```bash
brew tap flownative/flownative
$ brew install localbeach
$ beach version
```

### Install dockerized composer from flownative

As the PHP container from beach has no composer installed, we need to use the composer container from `flownative` to run the composer commands.
Therefore, we need to add a function to our `.bashrc` or `.zshrc`.

```bash
composer80 () {
    tty=
    tty -s && tty=--tty
    docker run \
        $tty \
        --interactive \
        --rm \
        --user $(id -u):$(id -g) \
        --volume /etc/passwd:/etc/passwd:ro \
        --volume /etc/group:/etc/group:ro \
        --volume $(pwd):/application:delegated \
        --volume $HOME/.composer/cache:/home/composer/cache:delegated \
        --volume $HOME/.composer/auth.json:/home/composer/auth.json \
        flownative/composer2:8.0 "$@"
}
```

After you added the function, you should run `source .zshrc` or `source .bashrc` depends on which shell you are using.

### Setup beach instance

Clone the repository and install via composer.

```bash
git clone https://github.com/neos/demo.neos.io.git your/Folder/demo.neos.io
cd your/Folder/demo.neos.io
composer80 update
```

Start the beach instance and if you run the instance.

```bash
beach start
```

If you run the instance for the first time, we also have to import the site.

```bash
beach setup-https
beach exec
./flow site:import Neos.Demo
```

After that, the instance should be available under https://demoneosio.localbeach.net


## Hosting

This website is hosted on [Flownative Beach](https://beach.flownative.com) in the Neos organization.
