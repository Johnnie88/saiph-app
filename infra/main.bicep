targetScope = 'subscription'

@minLength(1)
@maxLength(64)
@description('Name of the environment that can be used as part of naming resource convention')
param environmentName string

@minLength(1)
@description('Primary location for all resources')
param location string

param srcExists bool
@secure()
param srcDefinition object
param kirkiProHeadlineDividerExists bool
@secure()
param kirkiProHeadlineDividerDefinition object
param kirkiProInputSliderExists bool
@secure()
param kirkiProInputSliderDefinition object
param kirkiProMarginPaddingExists bool
@secure()
param kirkiProMarginPaddingDefinition object
param kirkiProResponsiveExists bool
@secure()
param kirkiProResponsiveDefinition object
param kirkiProTabsExists bool
@secure()
param kirkiProTabsDefinition object

@description('Id of the user or app to assign application roles')
param principalId string

// Tags that should be applied to all resources.
// 
// Note that 'azd-service-name' tags should be applied separately to service host resources.
// Example usage:
//   tags: union(tags, { 'azd-service-name': <service name in azure.yaml> })
var tags = {
  'azd-env-name': environmentName
}

var abbrs = loadJsonContent('./abbreviations.json')
var resourceToken = toLower(uniqueString(subscription().id, environmentName, location))

resource rg 'Microsoft.Resources/resourceGroups@2022-09-01' = {
  name: 'rg-${environmentName}'
  location: location
  tags: tags
}

module monitoring './shared/monitoring.bicep' = {
  name: 'monitoring'
  params: {
    location: location
    tags: tags
    logAnalyticsName: '${abbrs.operationalInsightsWorkspaces}${resourceToken}'
    applicationInsightsName: '${abbrs.insightsComponents}${resourceToken}'
  }
  scope: rg
}

module dashboard './shared/dashboard-web.bicep' = {
  name: 'dashboard'
  params: {
    name: '${abbrs.portalDashboards}${resourceToken}'
    applicationInsightsName: monitoring.outputs.applicationInsightsName
    location: location
    tags: tags
  }
  scope: rg
}

module registry './shared/registry.bicep' = {
  name: 'registry'
  params: {
    location: location
    tags: tags
    name: '${abbrs.containerRegistryRegistries}${resourceToken}'
  }
  scope: rg
}

module keyVault './shared/keyvault.bicep' = {
  name: 'keyvault'
  params: {
    location: location
    tags: tags
    name: '${abbrs.keyVaultVaults}${resourceToken}'
    principalId: principalId
  }
  scope: rg
}

module appsEnv './shared/apps-env.bicep' = {
  name: 'apps-env'
  params: {
    name: '${abbrs.appManagedEnvironments}${resourceToken}'
    location: location
    tags: tags
    applicationInsightsName: monitoring.outputs.applicationInsightsName
    logAnalyticsWorkspaceName: monitoring.outputs.logAnalyticsWorkspaceName
  }
  scope: rg
}

module src './app/src.bicep' = {
  name: 'src'
  params: {
    name: '${abbrs.appContainerApps}src-${resourceToken}'
    location: location
    tags: tags
    identityName: '${abbrs.managedIdentityUserAssignedIdentities}src-${resourceToken}'
    applicationInsightsName: monitoring.outputs.applicationInsightsName
    containerAppsEnvironmentName: appsEnv.outputs.name
    containerRegistryName: registry.outputs.name
    exists: srcExists
    appDefinition: srcDefinition
    allowedOrigins: [
      'https://${abbrs.appContainerApps}kirki-pro-in-${resourceToken}.${appsEnv.outputs.domain}'
      'https://${abbrs.appContainerApps}kirki-pro-ma-${resourceToken}.${appsEnv.outputs.domain}'
    ]
  }
  scope: rg
}

module kirkiProHeadlineDivider './app/kirki-pro-headline-divider.bicep' = {
  name: 'kirki-pro-headline-divider'
  params: {
    name: '${abbrs.appContainerApps}kirki-pro-he-${resourceToken}'
    location: location
    tags: tags
    identityName: '${abbrs.managedIdentityUserAssignedIdentities}kirki-pro-he-${resourceToken}'
    applicationInsightsName: monitoring.outputs.applicationInsightsName
    containerAppsEnvironmentName: appsEnv.outputs.name
    containerRegistryName: registry.outputs.name
    exists: kirkiProHeadlineDividerExists
    appDefinition: kirkiProHeadlineDividerDefinition
    allowedOrigins: [
      'https://${abbrs.appContainerApps}kirki-pro-in-${resourceToken}.${appsEnv.outputs.domain}'
      'https://${abbrs.appContainerApps}kirki-pro-ma-${resourceToken}.${appsEnv.outputs.domain}'
    ]
  }
  scope: rg
}

module kirkiProInputSlider './app/kirki-pro-input-slider.bicep' = {
  name: 'kirki-pro-input-slider'
  params: {
    name: '${abbrs.appContainerApps}kirki-pro-in-${resourceToken}'
    location: location
    tags: tags
    identityName: '${abbrs.managedIdentityUserAssignedIdentities}kirki-pro-in-${resourceToken}'
    applicationInsightsName: monitoring.outputs.applicationInsightsName
    containerAppsEnvironmentName: appsEnv.outputs.name
    containerRegistryName: registry.outputs.name
    exists: kirkiProInputSliderExists
    appDefinition: kirkiProInputSliderDefinition
    apiUrls: [
      src.outputs.uri
      kirkiProHeadlineDivider.outputs.uri
      kirkiProResponsive.outputs.uri
      kirkiProTabs.outputs.uri
    ]
  }
  scope: rg
}

module kirkiProMarginPadding './app/kirki-pro-margin-padding.bicep' = {
  name: 'kirki-pro-margin-padding'
  params: {
    name: '${abbrs.appContainerApps}kirki-pro-ma-${resourceToken}'
    location: location
    tags: tags
    identityName: '${abbrs.managedIdentityUserAssignedIdentities}kirki-pro-ma-${resourceToken}'
    applicationInsightsName: monitoring.outputs.applicationInsightsName
    containerAppsEnvironmentName: appsEnv.outputs.name
    containerRegistryName: registry.outputs.name
    exists: kirkiProMarginPaddingExists
    appDefinition: kirkiProMarginPaddingDefinition
    apiUrls: [
      src.outputs.uri
      kirkiProHeadlineDivider.outputs.uri
      kirkiProResponsive.outputs.uri
      kirkiProTabs.outputs.uri
    ]
  }
  scope: rg
}

module kirkiProResponsive './app/kirki-pro-responsive.bicep' = {
  name: 'kirki-pro-responsive'
  params: {
    name: '${abbrs.appContainerApps}kirki-pro-re-${resourceToken}'
    location: location
    tags: tags
    identityName: '${abbrs.managedIdentityUserAssignedIdentities}kirki-pro-re-${resourceToken}'
    applicationInsightsName: monitoring.outputs.applicationInsightsName
    containerAppsEnvironmentName: appsEnv.outputs.name
    containerRegistryName: registry.outputs.name
    exists: kirkiProResponsiveExists
    appDefinition: kirkiProResponsiveDefinition
    allowedOrigins: [
      'https://${abbrs.appContainerApps}kirki-pro-in-${resourceToken}.${appsEnv.outputs.domain}'
      'https://${abbrs.appContainerApps}kirki-pro-ma-${resourceToken}.${appsEnv.outputs.domain}'
    ]
  }
  scope: rg
}

module kirkiProTabs './app/kirki-pro-tabs.bicep' = {
  name: 'kirki-pro-tabs'
  params: {
    name: '${abbrs.appContainerApps}kirki-pro-ta-${resourceToken}'
    location: location
    tags: tags
    identityName: '${abbrs.managedIdentityUserAssignedIdentities}kirki-pro-ta-${resourceToken}'
    applicationInsightsName: monitoring.outputs.applicationInsightsName
    containerAppsEnvironmentName: appsEnv.outputs.name
    containerRegistryName: registry.outputs.name
    exists: kirkiProTabsExists
    appDefinition: kirkiProTabsDefinition
    allowedOrigins: [
      'https://${abbrs.appContainerApps}kirki-pro-in-${resourceToken}.${appsEnv.outputs.domain}'
      'https://${abbrs.appContainerApps}kirki-pro-ma-${resourceToken}.${appsEnv.outputs.domain}'
    ]
  }
  scope: rg
}

output AZURE_CONTAINER_REGISTRY_ENDPOINT string = registry.outputs.loginServer
output AZURE_KEY_VAULT_NAME string = keyVault.outputs.name
output AZURE_KEY_VAULT_ENDPOINT string = keyVault.outputs.endpoint
