export const useApiFetch = () => {
  const { $api } = useNuxtApp()
  return $api
}
