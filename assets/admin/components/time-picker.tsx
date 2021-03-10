import { h, FunctionComponent } from "preact";

type Props = {
  time: string;
};

const TimeRegex = /^[0-9]{1,2}:[0-9]{2}/;

const TimePicket: FunctionComponent<Props> = ({}) => {
  return (
    <div className="op__timePickerContainer">
      <input type="text" />
    </div>
  );
};
