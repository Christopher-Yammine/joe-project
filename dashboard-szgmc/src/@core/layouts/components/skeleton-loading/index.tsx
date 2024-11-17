import { Box, Skeleton } from '@mui/material'

interface SkeletonLoadingProps {
  pageType: 'overview' | 'historical'
}

const SkeletonLoading: React.FC<SkeletonLoadingProps> = ({ pageType }) => {
  if (pageType === 'overview') {
    return (
      <Box sx={{ width: '100%', height: '600px', display: 'flex', flexDirection: 'column', gap: 2 }}>
        <Skeleton variant='rectangular' width='100%' height={150} sx={{ borderRadius: 1 }} />

        <Box sx={{ display: 'flex', gap: 2 }}>
          <Skeleton variant='rectangular' width='33%' height={250} sx={{ borderRadius: 1 }} />
          <Skeleton variant='rectangular' width='33%' height={250} sx={{ borderRadius: 1 }} />
          <Skeleton variant='rectangular' width='33%' height={250} sx={{ borderRadius: 1 }} />
        </Box>

        <Box sx={{ display: 'flex', gap: 2 }}>
          <Skeleton variant='rectangular' width='50%' height={250} sx={{ borderRadius: 1 }} />
          <Skeleton variant='rectangular' width='50%' height={250} sx={{ borderRadius: 1 }} />
        </Box>
      </Box>
    )
  }

  if (pageType === 'historical') {
    return (
      <Box sx={{ width: '100%', height: '600px', display: 'flex', flexDirection: 'column', gap: 2 }}>
        <Box sx={{ display: 'flex', gap: 2 }}>
          <Skeleton variant='rectangular' width='50%' height={400} sx={{ borderRadius: 1 }} />
          <Skeleton variant='rectangular' width='50%' height={400} sx={{ borderRadius: 1 }} />
        </Box>
        <Box sx={{ display: 'flex', gap: 2 }}>
          <Skeleton variant='rectangular' width='50%' height={400} sx={{ borderRadius: 1 }} />
          <Skeleton variant='rectangular' width='50%' height={400} sx={{ borderRadius: 1 }} />
        </Box>
      </Box>
    )
  }

  // Ensure we return null if pageType doesn't match
  return null
}

export default SkeletonLoading
