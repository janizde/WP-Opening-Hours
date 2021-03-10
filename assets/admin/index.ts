import { render, h } from "preact";
import RootComponent from "./root-component";
import { SpecEntry } from "./types";

import "../../includes/jquery-ui-timepicker/jquery.ui.timepicker.js";

import "./legacy/ExtendedSettings";
import "./legacy/Holidays";
import "./legacy/IrregularOpenings";
import "./legacy/OpSet";
import "./legacy/Periods";
import "./legacy/ShortcodeBuilder";

import "./styles/index.scss";

window.addEventListener("load", () => {
  const rootEl = document.getElementById("op_opening_hours_specification");

  if (!rootEl) {
    return;
  }

  const specJSON = rootEl.innerText;
  let data: SpecEntry | null = null;

  try {
    data = JSON.parse(specJSON);
  } catch (e) {
    console.warn(`Tried to parse contents of #op_admin_ui_root as JSON but content is not parsable.`);
  }

  render(h(RootComponent, { spec: data }), rootEl);
});
