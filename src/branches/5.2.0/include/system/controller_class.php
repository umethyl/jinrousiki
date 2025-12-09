<?php
//-- コントローラー基底クラス --//
abstract class JinrouController {
  //実行
  final public static function Execute() {
    static::Start();
    static::Load();
    static::Run();
    static::Finish();
  }

  //初期処理
  final protected static function Start() {
    if (true === static::Unusable()) {
      static::OutputUnusableError();
    }
    static::Maintenance();
  }

  //実行不許可判定
  final protected static function Unusable() {
    if (true === static::IsAdmin()) {
      return true !== JinrouAdmin::Enable(static::GetAdminType());
    }
    if (true === static::IsTest()) {
      return true !== ServerConfig::DEBUG_MODE;
    }
    return static::IsUnusable();
  }

  //管理機能判定
  protected static function IsAdmin() {
    return false;
  }

  //管理機能名取得
  protected static function GetAdminType() {}

  //テスト機能判定
  protected static function IsTest() {
    return false;
  }

  //個別実行不許可判定
  protected static function IsUnusable() {
    return false;
  }

  //実行不許可エラー表示
  protected static function OutputUnusableError() {
    HTML::OutputUnusableError();
  }

  //保守処理
  protected static function Maintenance() {}

  //データロード
  final protected static function Load() {
    static::LoadRequest();
    static::LoadDB();
    static::LoadSession();
    static::LoadSetting();
    if (true === static::EnableLoadRoom()) {
      static::LoadRoom();
      static::LoadUser();
      static::LoadSelf();
    }
    static::LoadExtra();
  }

  //リクエストロード
  final protected static function LoadRequest() {
    if (true === static::EnableLoadRequest()) {
      $request = static::GetLoadRequest();
      if (null !== $request) {
	RQ::LoadRequest($request);
      } else {
	RQ::LoadRequest();
      }
      static::LoadRequestExtra();
    }
  }

  //リクエストロード有効判定
  protected static function EnableLoadRequest() {
    return true;
  }

  //リクエストクラス取得
  protected static function GetLoadRequest() {
    return null;
  }

  //追加リクエストロード
  protected static function LoadRequestExtra() {}

  //DB情報ロード
  final protected static function LoadDB() {
    if (true === static::EnableLoadDatabase()) {
      DB::Connect(static::GetLoadDatabaseID());
    }
  }

  //DB情報ロード有効判定
  protected static function EnableLoadDatabase() {
    return false;
  }

  //DB情報ロードID取得
  protected static function GetLoadDatabaseID() {
    return null;
  }

  //セッションロード
  protected static function LoadSession() {}

  //設定情報ロード
  protected static function LoadSetting() {}

  //村情報ロード有効判定
  protected static function EnableLoadRoom() {
    return false;
  }

  //村情報ロード
  protected static function LoadRoom() {}

  //ユーザ情報ロード
  protected static function LoadUser() {}

  //本人情報ロード
  protected static function LoadSelf() {}

  //追加情報ロード
  protected static function LoadExtra() {}

  //処理実行
  final protected static function Run() {
    static::OutputRunHeader();
    if (true === static::EnableCommand()) {
      static::RunCommand();
    } else {
      static::Output();
    }
    static::OutputRunFooter();
  }

  //実行前出力
  protected static function OutputRunHeader() {}

  //コマンド実行有効判定
  protected static function EnableCommand() {
    return false;
  }

  //コマンド実行
  protected static function RunCommand() {}

  //出力
  protected static function Output() {}

  //実行後出力
  protected static function OutputRunFooter() {}

  //終了処理
  protected static function Finish() {}
}
