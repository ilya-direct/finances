/*globals queryUtil:true*/
define(['lodash'], function (_) {
    /*eslint strict:0 */

    if (typeof window !== 'object') {
        return {
            load: function(name, parentRequire, onLoad) {
                onLoad(false);
            }
        };
    }

    var model = window.rendererModel || window.editorModel || {};
    var params = {
        experiments: true,
        experimentsoff: false
    };

    var experiments = _.mapValues(model.runningExperiments, function(value) {
        return value === 'new';
    });

    //TODO: Is this needed? It should be possible to add experiments to the vm from the QS with ?experiment=x:new&experiment=y:off
    experiments = _.reduce(params, function (soFar, paramValue, paramName) {
        var queryValue = queryUtil.getParameterByName(paramName);
        var paramExperiments = queryValue.split(',').filter(Boolean);
        var additions = _(paramExperiments).map(function (exp) {return [exp, paramValue]; }).zipObject().value();
        return _.assign(soFar, additions);
    }, experiments || {});

    return {
        load: function (name, parentRequire, onload) {
            onload(Boolean(experiments[name]));
        }
    };
});
