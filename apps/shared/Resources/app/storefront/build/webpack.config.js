const { join } = require('path');


module.exports = (params) => {
    return {
        resolve: {
            modules: [
                join(params.basePath, 'Resources/app/storefront/node_modules'),
            ],
        },
    };
};
