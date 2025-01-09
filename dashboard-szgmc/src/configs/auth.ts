const APIUrl = process.env.NEXT_PUBLIC_BASE_URL

export default {
  meEndpoint: `${APIUrl}/me`,
  loginEndpoint: `${APIUrl}/login`,
  registerEndpoint: '/jwt/register',
  storageTokenKeyName: 'accessToken',
  onTokenExpiration: 'refreshToken' // logout | refreshToken
}
