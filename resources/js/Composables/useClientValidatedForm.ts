import type { InertiaForm } from '@inertiajs/vue3'
import type { VisitOptions } from '@inertiajs/core'
import { useVuelidate } from '@vuelidate/core'

type InertiaFormRecord = Record<string | number | symbol, unknown>
type InertiaSubmissionMethod = (url: string, options?: Partial<VisitOptions>) => Promise<void>

// Extend InertiaForm and convert the originally synchronous `post`, `put`, `patch`, `delete` and `get` methods
// to asynchronous methods returning a Promise<void>.
export type ClientValidatedInertiaForm<T extends InertiaFormRecord> = Omit<
  InertiaForm<T>,
  'post' | 'put' | 'patch' | 'delete' | 'get'
> & {
  post: InertiaSubmissionMethod
  put: InertiaSubmissionMethod
  patch: InertiaSubmissionMethod
  delete: InertiaSubmissionMethod
  get: InertiaSubmissionMethod
}

/**
 * @description Enhances an Inertia form with client-side validation capabilities.
 *
 * This function wraps an Inertia form in a Proxy to intercept and extend its `post`, `put`, `patch`, `delete`, and `get`
 * methods with client-side validation logic using Vuelidate. When these methods are called, the Inertia form data is first
 * validated against the provided rules. If the data passes validation, the original method is called with
 * the same arguments. If validation fails, the form errors are set, and the method call is nullified.
 *
 * This enhancement allows seamless integration of Vuelidate with Inertia forms, providing client-side validation feedback
 * before form submission to the back-end. It is particularly useful when invoking the `post`, `put`, `patch`, `delete`,
 * and `get` methods provided by Inertia's `useForm` helper, which internally call the `submit` method.
 *
 * Note that because Vuelidate does validation asynchronously, the `post`, `put`, `patch`, `delete`, and `get`
 * of the Inertia form methods now return promises.
 *
 * @param rules - An object containing validation rules to apply to the InertiaForm instance.
 * @param inertiaForm - The InertiaForm instance to be validated.
 *
 * @example
 *
 * const form = useForm({ name: '', email: '' }) // Initial Inertia form usage without client-side validation
 * form.post('/submit')
 * console.log('Form Submitted!') // Prints after form.post('/submit') executes
 *
 * // After using useClientValidatedForm (with client-side validation)
 * import { required, email } from '@vuelidate/validators'
 *
 * const rules = { name: { required }, email: { required, email } }
 * const form = useClientValidatedForm(rules, useForm({ name: 'example', email: 'test@example.com' }))
 * const submit = async function () {
 *   await form.post('/submit')
 *   console.log('Done!') // Prints after form.post('/submit') promise resolves
 * }
 */
export const useClientValidatedForm = function <T extends InertiaFormRecord>(rules: object, inertiaForm: InertiaForm<T>) {
  return new Proxy(inertiaForm, {
    get: function (target, prop, receiver) {
      // Return the original property if it's not post, put, patch, delete, or get
      if (typeof prop !== 'string' || !['post', 'put', 'patch', 'delete', 'get'].includes(prop)) {
        return Reflect.get(target, prop, receiver)
      }

      // Configure the validator
      const validator = useVuelidate<T>(rules, target.data())
      target.clearErrors()

      // Return a function that handles validation before calling the original method
      return async function (url: string, options?: Partial<VisitOptions>) {
        const valid = await validator.value.$validate()

        // If valid, call the original method with the same arguments
        if (valid) {
          const originalMethod = Reflect.get(target, prop, receiver) as InertiaSubmissionMethod
          return originalMethod.call(target, url, options)
        }

        // Set the form errors if validation fails and return a function that does nothing
        for (const key of Object.keys(target.data())) {
          if (validator.value[key]?.$error) target.setError(key, validator.value[key].$errors[0].$message)
        }

        return () => {}
      }
    },
  }) as ClientValidatedInertiaForm<T>
}
