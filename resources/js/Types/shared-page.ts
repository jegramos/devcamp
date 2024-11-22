/**
 * The type of Inertia Shared Data used by the app
 * @see https://v2.inertiajs.com/shared-data
 */
export type SharedPage = {
  appName: string
  logoutUrl: string
  pageUris: {
    resume: string
    about: string
  }
  accountSettings: {
    theme: 'light' | 'dark' | 'auto'
    passkeys_enabled: boolean
    '2fa_enabled': boolean
  } | null
  auth: {
    user?: {
      full_name: string
      username: string
      email: string
      roles: string[]
      email_verified: boolean
      profile_picture_url: string | null
      nameInitials: string
      provider_name: string | null
      from_external_account: boolean
      recommend_username_change: boolean
    }
  }
  flash: {
    [key in SessionFlashKey]: string | null
  }
}

/**
 * The different types of error codes that the back-end
 * may return.
 */
export enum ErrorCode {
  INVALID_CREDENTIALS = 'INVALID_CREDENTIALS_ERROR',
  TOO_MANY_REQUESTS = 'TOO_MANY_REQUESTS_ERROR',
  EMAIL_ALREADY_VERIFIED = 'EMAIL_ALREADY_VERIFIED_ERROR',
  VALIDATION = 'VALIDATION_ERROR',
  RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND_ERROR',
  UNAUTHORIZED = 'UNAUTHORIZED_ERROR',
  UNKNOWN_ROUTE = 'UNKNOWN_ROUTE_ERROR',
  SERVER = 'SERVER_ERROR',
  PAYLOAD_TOO_LARGE = 'PAYLOAD_TOO_LARGE_ERROR',
  EXTERNAL_ACCOUNT_EMAIL_CONFLICT = 'EXTERNAL_ACCOUNT_EMAIL_CONFLICT_ERROR',
  ACCOUNT_DEACTIVATED = 'ACCOUNT_DEACTIVATED_ERROR',
  BAD_REQUEST = 'BAD_REQUEST_ERROR',
}

/**
 * The different session flash keys from the back-end
 */
export enum SessionFlashKey {
  CMS_SUCCESS = 'CMS_SUCCESS',
  CMS_ERROR = 'CMS_ERROR',
  CMS_LOGIN_SUCCESS = 'CMS_LOGIN_SUCCESS',
  CMS_EMAIL_VERIFIED = 'CMS_EMAIL_VERIFIED',
  CMS_EMAIL_UPDATE_CONFIRMED = 'CMS_EMAIL_UPDATE_CONFIRMED',
}
