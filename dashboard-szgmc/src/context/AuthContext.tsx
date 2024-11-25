// ** React Imports
import { createContext, useEffect, useState, ReactNode } from 'react'

// ** Next Import
import { useRouter } from 'next/router'

// ** Axios
import axios from 'axios'

// ** Config
import authConfig from 'src/configs/auth'
import { config } from 'src/configs/config'

// ** Types
import { AuthValuesType, LoginParams, ErrCallbackType, UserDataType } from './types'

// ** Defaults
const defaultProvider: AuthValuesType = {
  user: null,
  loading: true,
  setUser: () => null,
  setLoading: () => Boolean,
  login: () => Promise.resolve(),
  logout: () => Promise.resolve()
}

const AuthContext = createContext(defaultProvider)

type Props = {
  children: ReactNode
}

const AuthProvider = ({ children }: Props) => {
  // ** States
  const [user, setUser] = useState<UserDataType | null>(defaultProvider.user)
  const [loading, setLoading] = useState<boolean>(defaultProvider.loading)

  // ** Hooks
  const router = useRouter()
  const API_URL = config.NEXT_PUBLIC_BASE_URL

  useEffect(() => {
    const initAuth = async (): Promise<void> => {
      const storedToken = window.localStorage.getItem(authConfig.storageTokenKeyName)!
      if (storedToken) {
        setLoading(true)
        await axios
          .get(authConfig.meEndpoint, {
            headers: {
              Authorization: storedToken
            }
          })
          .then(async response => {
            setLoading(false)
            setUser({ ...response.data.userData })
          })
          .catch(() => {
            localStorage.removeItem('userData')
            localStorage.removeItem('refreshToken')
            localStorage.removeItem('accessToken')
            setUser(null)
            setLoading(false)
            if (authConfig.onTokenExpiration === 'logout' && !router.pathname.includes('login')) {
              router.replace('/login')
            }
          })
      } else {
        setLoading(false)
      }
    }

    initAuth()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  // const handleLogin = (params: LoginParams, errorCallback?: ErrCallbackType) => {
  //   console.log('entered')
  //   console.log('🚀 ~ handleLogin ~ authConfig.loginEndpoint:', `${API_URL}/login`)
  //   console.log('🚀 ~ handleLogin ~ params:', params)

  //   axios
  //     .post(`${API_URL}/login`, params)
  //     .then(async response => {
  //       console.log('🚀 ~ handleLogin ~ response:', response)
  //       params.rememberMe
  //         ? window.localStorage.setItem(authConfig.storageTokenKeyName, response.data.accessToken)
  //         : null
  // const returnUrl = router.query.returnUrl

  // console.log('🚀 ~ handleLogin ~ returnUrl:', returnUrl)
  // setUser({ ...response.data.userData })
  // params.rememberMe ? window.localStorage.setItem('userData', JSON.stringify(response.data.userData)) : null

  // const redirectURL = returnUrl && returnUrl !== '/' ? returnUrl : '/'

  // router.replace(redirectURL as string)
  // })

  //     .catch(err => {
  //       if (errorCallback) errorCallback(err)
  //     })
  // }

  const handleLogin = async (params: LoginParams, errorCallback?: ErrCallbackType): Promise<void> => {
    console.log('🚀 Entered handleLogin')
    console.log('🚀 ~ Login params:', params)

    try {
      const response = await fetch(`${API_URL}/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(params)
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      console.log('🚀 ~ Login successful, data:', data)

      if (params.rememberMe) {
        console.log('🚀 ~ handleLogin ~ data.authorization:', data.authorisation.token)
        window.localStorage.setItem(authConfig.storageTokenKeyName, data.authorisation.token)
        window.localStorage.setItem('userData', JSON.stringify(data.user))
      }

      const returnUrl = router.query.returnUrl

      console.log('🚀 ~ handleLogin ~ returnUrl:', returnUrl)
      setUser(data.user)
      params.rememberMe ? window.localStorage.setItem('user', JSON.stringify(data.user)) : null

      const redirectURL = returnUrl && returnUrl !== '/' ? returnUrl : '/home'

      router.replace(redirectURL as string)
    } catch (error: unknown) {
      console.error('🚨 Error during login:', error)

      if (error instanceof Error) {
        console.error('🚨 Error details:', error.message)

        if (errorCallback) {
          errorCallback({ message: error.message })
        }
      } else {
        console.error('🚨 An unexpected error occurred.')
      }
    }
  }

  const handleLogout = () => {
    setUser(null)
    window.localStorage.removeItem('userData')
    window.localStorage.removeItem(authConfig.storageTokenKeyName)
    router.push('/login')
  }

  const values = {
    user,
    loading,
    setUser,
    setLoading,
    login: handleLogin,
    logout: handleLogout
  }

  return <AuthContext.Provider value={values}>{children}</AuthContext.Provider>
}

export { AuthContext, AuthProvider }
