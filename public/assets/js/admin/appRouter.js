define([
  'underscore',
  'backbone',
  'marionette',
], function(_, Backbone, Marionette){

  return Backbone.Marionette.AppRouter.extend({

    appRoutes: {
      "": "index",
      "settings": "settings",
      "lrs": "lrs",
      "users": "users",
      "apps": "apps"
    }

  });

});