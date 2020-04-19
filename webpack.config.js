const path = require("path");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = (env, argv) => {
  const mode = process.env.NODE_ENV || "development";
  const isDevelopment = mode === "development";

  const config = {
    mode,
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
        {
          test: /\.scss$/,
          loader: [
            isDevelopment ? "style-loader" : MiniCssExtractPlugin.loader,
            {
              loader: "css-loader",
              options: {
                sourceMap: isDevelopment,
              },
            },
            {
              loader: "sass-loader",
              options: {
                sourceMap: isDevelopment,
              },
            },
          ],
        },
      ],
    },
    plugins: [
      new MiniCssExtractPlugin({
        filename: "[name].css",
        chunkFilename: "[id].css",
      }),
    ],
  };

  return config;
};
