/*
 * Copyright (c) 2022.
 * Create by cocomine
 */
define(() => {
    class MapAutoFit {
        constructor(origin, destination) {
            this.origin = origin;
            this.destination = destination;
        }

        onAdd(map) {
            this._map = map;
            this._container = document.createElement('div');
            this._container.className = 'mapboxgl-ctrl mapboxgl-ctrl-group';
            this._container.innerHTML = `<button><i class="fa-solid fa-up-right-and-down-left-from-center" style="font-size: 1.2em; padding-top: 4px"></i></button>`
            this._container.addEventListener('click', () => {
                map.fitBounds([this.origin, this.destination], {padding: 40});
            })
            return this._container;
        }

        onRemove() {
            this._container.parentNode.removeChild(this._container);
            this._map = undefined;
        }
    }

    return MapAutoFit;
})
