module.exports = {
    root: true,
    env: {
        node: true,
    },
    extends: [
        "eslint:recommended",
        "plugin:prettier/recommended",
        "plugin:vue/essential",
        "plugin:vue/strongly-recommended",
        "plugin:vue/recommended",
        "prettier",
        "prettier/vue",
    ],
    plugins: ["prettier"],
    rules: {
        "no-console": "off",
        "no-debugger": "off",
        // "no-console": process.env.NODE_ENV === "production" ? "error" : "off",
        // "no-debugger": process.env.NODE_ENV === "production" ? "error" : "off",
        "no-empty": [2, { allowEmptyCatch: true }],
        "vue/no-lone-template": "off",
        "vue/no-multi-spaces": "error",
        "vue/no-v-html": "off",
        "prettier/prettier": "error",
    },
    parserOptions: {
        parser: "babel-eslint",
    },
}
