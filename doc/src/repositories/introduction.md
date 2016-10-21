# Easier access to data

While very simple applications may work fine by just using the result set client, more complex applications may require
to organize data into actual entities. For the purpose of this, SimpleFM offers repositories which take care of all the
internals and let the user concentrate on the actual data.

A repository instance is always responsible for a single type of entity, letting you insert, update and delete an entity
from the database layout, as well as giving you multiple ways to query for records.
