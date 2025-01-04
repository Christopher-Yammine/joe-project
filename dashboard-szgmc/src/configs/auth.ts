const APIUrl = process.env.NEXT_PUBLIC_BASE_URL

export default {
  // meEndpoint: '/auth/me',
  meEndpoint: `${APIUrl}/me`,
  // loginEndpoint: '/jwt/login',
  loginEndpoint: `${APIUrl}/login`,
  registerEndpoint: '/jwt/register',
  storageTokenKeyName: 'accessToken',
  onTokenExpiration: 'refreshToken' // logout | refreshToken
}
