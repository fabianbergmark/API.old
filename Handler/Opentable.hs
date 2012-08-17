{-# LANGUAGE TupleSections, OverloadedStrings #-}
module Handler.Opentable where

import Import
import System.FilePath ((</>))
import Control.Monad (forM)
import System.Directory (doesDirectoryExist, getDirectoryContents)
import Data.Aeson (Value, encode, object, (.=))

getOpentablesR = do
  tables <- liftIO . getTables $ "static/yql-tables"
  jsonToRepJson . encode $ tables
  
getTables :: FilePath -> IO [FilePath]
getTables dir = do
  content <- getDirectoryContents dir
  let properContent = filter (`notElem` ["." :: FilePath, ".."]) content
  paths <- forM properContent $ \name -> do
    let path = dir </> name
    isDirectory <- doesDirectoryExist path
    if isDirectory
      then getTables path
      else return [path]
  return (concat paths)
