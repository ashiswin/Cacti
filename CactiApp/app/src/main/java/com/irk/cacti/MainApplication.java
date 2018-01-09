package com.irk.cacti;

import android.app.Application;

import com.android.volley.RequestQueue;

/**
 * Created by ashis on 9/30/2017.
 */

public class MainApplication extends Application {
    public static final String SERVER_HOST = "http://devostrum.no-ip.info/cacti";
    RequestQueue requestQueue;
    int userId;

    String name;
    String email;
}
