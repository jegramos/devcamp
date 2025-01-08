import { useDateFormat } from '@vueuse/core'
import type { ComputedRef } from 'vue'

/**
 * Formats a period of time into a human-readable string.

 * @param period An array containing the start date and optionally the end date.
 * @param format The desired date format (e.g., 'MMM YYYY', 'dd/MM/yyyy'). see https://vueuse.org/shared/useDateFormat/
 */
export const formatDateSpan = function (period: Array<string>, format = 'MMM YYYY'): string {
  if (period.length > 2 || period.length < 1) {
    throw new Error('The period must have 1 or 2 elements')
  }

  const startDate = useDateFormat(period[0], format).value

  if (period.length === 1) {
    const endDate = 'Present'
    return `${startDate} - ${endDate}`
  }

  let endDate: Date | string | ComputedRef<string> = new Date(period[1])
  if (endDate.toString() === 'Invalid Date') endDate = 'Present'
  else endDate = useDateFormat(endDate, format).value

  return `${startDate} - ${endDate}`
}
