const clarauuid = "7ef4fd45-fded-4f58-a73f-b5fbf519ddea";
function getJSON(path, success, params) {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', path, true);
  xhr.onload = function() {
    if (xhr.status >= 200 && xhr.status < 400) {
      success(JSON.parse(xhr.responseText), params);
    }
  };
  xhr.send();
}

function searchJSON(src, target) {
  for (var i = src.length - 1; i >= 0; i--) {
    if(src[i].type === 'Player') {
      return src[i].operators[0].configurator;
    }
  }
  return null;
}

function loadPlayer(scene, params) {
  // process scene json to get presets
  const configurator = searchJSON(scene.plugs, 'configurator');
  if (!configurator)  return;
  const configuratorJSON = JSON.parse(configurator);
  const presets = configuratorJSON.presets;
  if (!presets) return;
  for (var i = presets.length - 1; i >= 0; i--) {
    if (params.product_name.toLowerCase().includes(presets[i].name.toLowerCase())) {
      if (!window.clara) {
        document.getElementById('clara-embed').style.height = "200px";
        var clara = claraplayer('clara-embed');

        clara.on('loaded', () => {
          window.clara = clara;
        });
        clara.sceneIO.fetch(clarauuid).then(()=>{
          clara.sceneIO.useScene(clarauuid);
          clara.player.displayThumbnail(true, {preset: presets[i].name, width: 400
            , height: 300});

          clara.configuration.initConfiguration({productTour: false});
          //clara.configuration.initConfiguration({form: "PaletteEnvy - Length Preset"});

          ['fullscreen', 'home', 'vrSettings', 'orbit', 'zoom'].map(
            clara.player.hideTool
          );

          ['pan'].map(clara.player.removeTool);

          clara.commands.setCommandOptions('orbit', {
            turnTable: true,
            mobileOnly: false,
            touchVerticalDefault: true
          });

          // clara.commands.addCommand({
          //   enabled: true,
          //   active: true,
          //   tool: {
          //     drag: function(ev){
          //       var bb = ev.rect;
          //       return {
          //         momentum: false,
          //         handle: function(ev){
          //           var degrees = 360*ev.deltaX/bb.width;
          //           var rotation = clara.scene.get({name:'leggings_v5', plug: 'Transform', property: 'rotation'});

          //           clara.scene.set({name:'leggings_v5', plug: 'Transform', property: 'rotation'},{x: rotation.x, y:rotation.y+degrees, z:rotation.z});
          //         }
          //       };
          //     },
          //   }
          // }, 'planeRotate');

          clara.configuration.applyPreset(presets[i].name);

          //listen to option change
          const lengthDropdown = document.getElementsByName('product_options[length]');
          if (lengthDropdown) {
            lengthDropdown[0].addEventListener('change', (ev) => {
              clara.configuration.executeAttribute('Length', ev.target.value);
            });
          }
        });
      }
      break;
    }
  }
}

function initClara(params) {
  // fetch preset names
  getJSON('https://clara.io/api/scenes/' + clarauuid, loadPlayer, params);
}

(function() {
  var opts = {
    product_name          : php_vars.name
  };
  initClara(opts);
}());
