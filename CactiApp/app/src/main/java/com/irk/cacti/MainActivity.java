package com.irk.cacti;

import android.app.ProgressDialog;
import android.content.Intent;
import android.content.SharedPreferences;
import android.preference.PreferenceManager;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

import static com.irk.cacti.R.id.edtUsername;

public class MainActivity extends AppCompatActivity {
    private static final int REGISTER_INTENT = 0;

    MainApplication m;

    EditText edtUsername, edtPassword;
    Button btnLogin, btnRegister;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        getSupportActionBar().hide();

        m = (MainApplication) getApplicationContext();
        if(m.requestQueue == null) {
            m.requestQueue = Volley.newRequestQueue(MainActivity.this);
        }

        SharedPreferences preferences = PreferenceManager.getDefaultSharedPreferences(MainActivity.this);
        if(preferences.getInt("userId", -1) != -1) {
            m.userId = preferences.getInt("userId", -1);

            Intent dashboardIntent = new Intent(MainActivity.this, DashboardActivity.class);
            startActivity(dashboardIntent);
            finish();
        }
        edtUsername = (EditText) findViewById(R.id.edtUsername);
        edtPassword = (EditText) findViewById(R.id.edtPassword);

        btnLogin = (Button) findViewById(R.id.btnLogin);
        btnRegister = (Button) findViewById(R.id.btnRegister);

        btnLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                final String username = edtUsername.getText().toString();
                final String password = edtPassword.getText().toString();

                if(username.isEmpty()) {
                    edtUsername.setError("Please enter a username");
                    return;
                }

                if(password.isEmpty()) {
                    edtPassword.setError("Please enter a password");
                    return;
                }

                final ProgressDialog builder = new ProgressDialog(MainActivity.this);
                builder.setTitle("Logging in");
                builder.setMessage("Please wait while we log you in");
                builder.setCancelable(false);
                builder.setIndeterminate(true);
                builder.show();

                StringRequest loginRequest = new StringRequest(Request.Method.POST, m.SERVER_HOST + "/scripts/Login.php", new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        builder.cancel();

                        try {
                            JSONObject result = new JSONObject(response);

                            if(!result.getBoolean("success")) {
                                Toast.makeText(MainActivity.this, result.getString("message"), Toast.LENGTH_LONG).show();
                            }
                            else {
                                m.userId = result.getInt("userId");
                                finishLogin();
                            }
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                }, new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        Toast.makeText(MainActivity.this, error.getMessage(), Toast.LENGTH_LONG).show();
                    }
                }){
                    @Override
                    protected Map<String,String> getParams(){
                        Map<String,String> params = new HashMap<>();
                        params.put("username", username);
                        params.put("password",password);

                        return params;
                    }

                    @Override
                    public Map<String, String> getHeaders() throws AuthFailureError {
                        Map<String,String> params = new HashMap<>();
                        params.put("Content-Type","application/x-www-form-urlencoded");
                        return params;
                    }
                };
                m.requestQueue.add(loginRequest);
            }
        });
        btnRegister.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent registerIntent = new Intent(MainActivity.this, RegistrationActivity.class);
                registerIntent.putExtra("username", edtUsername.getText().toString());
                startActivityForResult(registerIntent, REGISTER_INTENT);
            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);

        if(requestCode == REGISTER_INTENT && resultCode == RESULT_OK) {
            finishLogin();
        }
    }

    public void finishLogin() {
        SharedPreferences preferences = PreferenceManager.getDefaultSharedPreferences(MainActivity.this);
        SharedPreferences.Editor editor = preferences.edit();

        editor.putInt("userId", m.userId);
        editor.apply();

        Intent dashboardIntent = new Intent(MainActivity.this, DashboardActivity.class);
        startActivity(dashboardIntent);
        finish();
    }
}
