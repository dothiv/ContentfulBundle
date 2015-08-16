# DothivContentfulBundle

[![Travis CI](https://travis-ci.org/dothiv/DothivContentfulBundle.svg?branch=master)](https://travis-ci.org/dothiv/DothivContentfulBundle)

[![Code Climate](https://codeclimate.com/github/dothiv/DothivContentfulBundle/badges/gpa.svg)](https://codeclimate.com/github/dothiv/DothivContentfulBundle)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d3e8ebf3-1c0c-4696-9ab4-5dc9bc437885/big.png)](https://insight.sensiolabs.com/projects/d3e8ebf3-1c0c-4696-9ab4-5dc9bc437885)

This is a Symfony2 bundle for providing a local queryable cache of [Contentful](https://www.contentful.com/) entries and assets.

It's a subtree split off [dothiv/dothiv](https://github.com/dothiv/dothiv).

## Setup

The cache needs a database.

    # Create database if not created before
    sudo su postgres
    psql
    CREATE USER contentful;
    CREATE DATABASE contentful;
    GRANT ALL PRIVILEGES ON DATABASE contentful TO contentful;
    ALTER USER contentful WITH PASSWORD 'password';

    # Update the schema
    app/console doctrine:schema:update --force
    
## Usage

### Sync content

Use 
    app/console contentful:sync <spaceid> <access_token>
to make your content available locally.

### PageController

[`PageController`](/Controller/PageController.php) contains a controller which can create the
correct cache headers. As it only listens on contentful item dates it is required to define a
minimum modification for the app. Run this command after every deploy to set it:

    app/console contentful:config last_modified_content.min_last_modified `date +%FT%T%z`
