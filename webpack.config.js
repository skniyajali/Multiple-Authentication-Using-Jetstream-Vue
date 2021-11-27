const path = require("path");

module.exports = {
    output: {
        chunkFilename: "js/[name].js?id=[chunkhash]",
    },
    resolve: {
        alias: {
            "@": path.resolve("resources/js"),
        },
        extensions: [
            ".wasm",
            ".mjs",
            ".js",
            ".jsx",
            ".vue", // 👈 note this entry
            ".json",
        ],
    },
};
