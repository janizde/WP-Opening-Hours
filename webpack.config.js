const path = require("path");

module.exports = (env, argv) => {
  const config = {
    mode: process.env.NODE_ENV || "development",
    entry: {
      admin: ["./assets/admin/index.ts"],
    },
    output: {
      filename: "[name].js",
      path: path.resolve(__dirname, "dist"),
    },
    resolve: {
      extensions: [".js", ".ts", ".tsx"],
    },
    devtool: argv.mode === "production" ? "source-map" : "cheap-eval-source-map",
    module: {
      rules: [
        {
          test: /\.tsx?$/,
          loader: "ts-loader",
          exclude: /node_modules/,
        },
      ],
    },
  };

  return config;
};
