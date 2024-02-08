(function (wp) {
    var el = wp.element.createElement;

    BooklyShortcodesL10n.forms.forEach(function(f) {
        wp.blocks.registerBlockType('bookly/' + f.type + '-' + f.token, {
            title: BooklyShortcodesL10n.block[f.type].title + ' - ' + f.name,
            description: BooklyShortcodesL10n.block[f.type].description,
            icon: el('svg', {width: '20', height: '20', viewBox: '0 0 64 64'},
                el('path', {style: {fill: f.color, stroke: 'rgb(0, 0, 0)'}, d: 'M 8 0 H 56 A 8 8 0 0 1 64 8 V 22 H 0 V 8 A 8 8 0 0 1 8 0 Z'}),
                el('path', {style: {fill: 'rgb(245, 245, 245)', stroke: 'rgb(0, 0, 0)'}, d: 'M 0 22 H 64 V 56 A 8 8 0 0 1 56 64 H 8 A 8 8 0 0 1 0 56 V 22 Z'}),
                el('rect', {style: {fill: f.color, stroke: 'rgb(0, 0, 0)' }, x: 10, y: 34, width: 16, height: 16}),
                el('rect', {style: {fill: f.color, stroke: 'rgb(0, 0, 0)' }, x: 38, y: 34, width: 16, height: 16}),
            ),
            category: 'bookly-blocks',
            keywords: [
                'bookly',
            ],
            supports: {
                customClassName: false,
                html: false
            },
            attributes: {},
            edit: function(props) {
                return [
                    el('svg', {width: '30', height: '30', viewBox: '0 0 64 64'},
                        el('path', {style: {fill: f.color, stroke: 'rgb(0, 0, 0)'}, d: 'M 8 0 H 56 A 8 8 0 0 1 64 8 V 22 H 0 V 8 A 8 8 0 0 1 8 0 Z'}),
                        el('path', {style: {fill: 'rgb(245, 245, 245)', stroke: 'rgb(0, 0, 0)'}, d: 'M 0 22 H 64 V 56 A 8 8 0 0 1 56 64 H 8 A 8 8 0 0 1 0 56 V 22 Z'}),
                        el('rect', {style: {fill: f.color, stroke: 'rgb(0, 0, 0)' }, x: 10, y: 34, width: 16, height: 16}),
                        el('rect', {style: {fill: f.color, stroke: 'rgb(0, 0, 0)' }, x: 38, y: 34, width: 16, height: 16}),
                    ),
                    el('span', {style: {'vertical-align': 'top', 'margin-left': '10px'}},
                        '[bookly-' + f.type + (f.token ? ' ' : '') + f.token + ']'
                    )
                ]
            },

            save: function(props) {
                return (
                    el('div', {},
                        '[bookly-' + f.type + (f.token ? ' ' : '') + f.token + ']'
                    )
                )
            }
        })
    });
})(
    window.wp
);