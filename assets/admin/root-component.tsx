import { FunctionComponent, h, Fragment } from "preact";
import { useMemo, createPortal } from "preact/compat";
import { SpecEntry, SpecKind } from "./types";

type Props = {
  /** Root spec entry or `null` if none exists */
  spec: SpecEntry | null;
};

const Holidays: FunctionComponent<Props> = () => null;
const DayOverrides: FunctionComponent<Props> = () => null;
const RecurringPeriods: FunctionComponent<Props> = () => null;

const RootComponent: FunctionComponent<Props> = ({ spec }) => {
  const elements = useMemo(
    () => ({
      [SpecKind.dayOverride]: document.getElementById("op_meta_box_day_override"),
      [SpecKind.holiday]: document.getElementById("op_meta_box_holidays"),
      [SpecKind.recurringPeriods]: document.getElementById("op_meta_box_recurring_periods"),
    }),
    []
  );

  return (
    <Fragment>
      {createPortal(<Holidays spec={spec} />, elements[SpecKind.holiday])}
      {createPortal(<DayOverrides spec={spec} />, elements[SpecKind.dayOverride])}
      {createPortal(<RecurringPeriods spec={spec} />, elements[SpecKind.recurringPeriods])}
    </Fragment>
  );
};

export default RootComponent;
