import { render, h } from "preact";
import RootComponent from "./root-component";

import "./styles.scss";

render(h(RootComponent, {}), document.getElementById("root"));
