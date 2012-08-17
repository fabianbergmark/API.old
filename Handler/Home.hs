{-# LANGUAGE TupleSections, OverloadedStrings #-}
module Handler.Home where

import Import

getHomeR :: Handler RepHtml
getHomeR = do
 defaultLayout $ do
  addScript $ StaticR js_functions_js
  addScript $ StaticR js_jquery_js
  addScript $ StaticR js_jquery_ui_js
  addScript $ StaticR js_bootstrap_js
  setTitle "API"
  $(widgetFile "homepage")
