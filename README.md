# Event Map

## Getting started

To get up and running, first install [Vagrant](https://www.vagrantup.com) and then just run:

`vagrant up`

You'll be able to see the dashboard running at `http://localhost:9000`.  To stop the server (so that port 9000 becomes available again) run:

`vagrant stop`

Under the hood, when you run `vagrant up`, the app is using [Vagrant](https://www.vagrantup.com) to create and run inside a virtual machine.  Read more about it [here](https://docs.vagrantup.com/v2/).

## Deploying

1. Set the AWS keys as environment variables.  You'll need to ask Saikat or Rapi for these.
2. Run `mkvirtualenv bernieevents`
3. Run `fab deploy`
