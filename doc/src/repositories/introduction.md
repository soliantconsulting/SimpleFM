# Easier access to data

While very simple applications can work fine just using the REST client, it is a best practice to organize data
into entities with associations. To facilitate this, SimpleFM provides repositories which take care of the internals
and let the application developer concentrate on the business domain.

A repository instance is responsible for a single type of entity. It is where you define the infrastructure to insert,
update, and delete an entity, as well as giving you multiple ways to query.

The FileMaker data API exposes data via layouts. We'll come back to this in more detail later, but it's important to know
that entities, repositories, and layouts must be created and maintained in concert with each other.
