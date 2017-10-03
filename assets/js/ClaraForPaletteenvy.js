function initClara(params) {
  console.log(params);
  if (!window.clara) {
    var clara = claraplayer('clara-embed');

    clara.on('loaded', () => {
      window.clara = clara;
    });

    clara.sceneIO.fetchAndUse("7ef4fd45-fded-4f58-a73f-b5fbf519ddea").then(()=>{
      ['fullscreen', 'home', 'vrSettings', 'orbit', 'zoom'].map(
        clara.player.hideTool
      );

      ['pan'].map(clara.player.removeTool);

      clara.commands.setCommandOptions('orbit', {
        turnTable: true,
        mobileOnly: true,
        touchVerticalDefault: true
      });
    });
  }
}

(function() {
  var opts = {
    product_name          : php_vars.name
  };
  initClara(opts);
}());
