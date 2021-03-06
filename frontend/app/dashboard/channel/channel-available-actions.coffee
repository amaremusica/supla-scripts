angular.module('supla-scripts').constant 'CHANNEL_AVAILABLE_ACTIONS', do ->
  actions =
    FNC_POWERSWITCH: [
      {label: 'włącz', action: 'turnOn', isActive: (channel) -> channel.on}
      {label: 'wyłącz', action: 'turnOff', isActive: (channel) -> channel.on == false}
      {label: 'przełącz', action: 'toggle', isActive: -> no}
    ]
    FNC_CONTROLLINGTHEGATE: [
      {label: 'otwórz/zamknij', action: 'toggle', isActive: -> no}
    ]
    FNC_CONTROLLINGTHEGATEWAYLOCK: [
      {label: 'otwórz', action: 'toggle', isActive: -> no}
    ]
    FNC_DIMMERANDRGBLIGHTING: [
      {label: 'wyłącz', action: 'setRgb,1,0,0', isActive: (channel) -> (channel.color_brightness != undefined || channel.brightness != undefined) && !channel.color_brightness && !channel.brightness}
      {label: 'biały', action: 'setRgb,ffffff,0,0', noButton: yes, isActive: -> no}
      {label: 'żółty', action: 'setRgb,ffff00,0,0', noButton: yes, isActive: -> no}
      {label: 'czerwony', action: 'setRgb,ff0000,0,0', noButton: yes, isActive: -> no}
      {label: 'pomarańczowy', action: 'setRgb,ff8800,0,0', noButton: yes, isActive: -> no}
      {label: 'jasny niebieski', action: 'setRgb,ff00ff,0,0', noButton: yes, isActive: -> no}
      {label: 'niebieski', action: 'setRgb,0000ff,0,0', noButton: yes, isActive: -> no}
      {label: 'różowy', action: 'setRgb,00ffff,0,0', noButton: yes, isActive: -> no}
      {label: 'zielony', action: 'setRgb,00ff00,0,0', noButton: yes, isActive: -> no}
      {label: 'losowy', action: 'setRgb,random,0,0', isActive: -> no}
    ]
    FNC_CONTROLLINGTHEROLLERSHUTTER: [
      {label: 'odsłoń', action: 'reveal', isActive: -> no}
      {label: 'zasłoń', action: 'shut', isActive: -> no}
    ]

  actions.FNC_LIGHTSWITCH = actions.FNC_POWERSWITCH
  actions.FNC_CONTROLLINGTHEGARAGEDOOR = actions.FNC_CONTROLLINGTHEGATE
  actions.FNC_CONTROLLINGTHEDOORLOCK = actions.FNC_CONTROLLINGTHEGATEWAYLOCK

  actions
