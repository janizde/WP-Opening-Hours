export enum SpecKind {
  dayOverride = "dayOverride",
  holiday = "holiday",
  recurringPeriods = "recurringPeriods",
}

export interface Holiday {
  kind: SpecKind.holiday;
  name: string;
  start: string;
  end: string;
}

export interface Period {
  start: string;
  end: string;
}

export interface RecurringPeriod {
  startTime: string;
  duration: number;
  weekday: number;
}

export interface DayOverride {
  kind: SpecKind.dayOverride;
  name: string;
  date: string;
  periods: Period[];
}

export interface RecurringPeriods {
  kind: SpecKind.recurringPeriods;
  start: string;
  end: string;
  periods: RecurringPeriod[];
  children: SpecEntry[];
}

export type SpecEntry = Holiday | RecurringPeriod | DayOverride;
