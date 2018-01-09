package com.irk.cacti;

import android.app.ProgressDialog;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.widget.EditText;

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

public class JoinInstitutionActivity extends AppCompatActivity {
    MainApplication m;

    EditText edtInviteCode;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_join_institution);

        m = (MainApplication) getApplicationContext();
        if(m.requestQueue == null) {
            m.requestQueue = Volley.newRequestQueue(JoinInstitutionActivity.this);
        }

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        edtInviteCode = (EditText) findViewById(R.id.edtInviteCode);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        MenuInflater inflater = getMenuInflater();
        inflater.inflate(R.menu.menu_join_institution, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle item selection
        switch (item.getItemId()) {
            case android.R.id.home:
                setResult(RESULT_OK);
                finish();
            case R.id.itmDone:
                joinInstitution();
                return true;
            default:
                return super.onOptionsItemSelected(item);
        }
    }

    public void joinInstitution() {
        final String inviteCode = edtInviteCode.getText().toString();

        if(inviteCode.isEmpty()) {
            edtInviteCode.setError("Please enter your invite code");
            return;
        }
        ProgressDialog dialog = new ProgressDialog(JoinInstitutionActivity.this);
        dialog.setIndeterminate(true);
        dialog.setTitle("Joining Institution");
        dialog.setMessage("Please wait while we join your institution");
        dialog.setCancelable(false);

        StringRequest joinRequest = new StringRequest(Request.Method.POST, m.SERVER_HOST + "/scripts/JoinInstitution.php", new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    Log.d("JOININST", response);
                    JSONObject o = new JSONObject(response);
                    if(o.getBoolean("success")) {
                        setResult(RESULT_OK);
                        finish();
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }

            }
        }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

            }
        }) {
            @Override
            protected Map<String,String> getParams(){
                Map<String,String> params = new HashMap<>();
                params.put("userId", m.userId + "");
                params.put("inviteCode", inviteCode);

                return params;
            }

            @Override
            public Map<String, String> getHeaders() throws AuthFailureError {
                Map<String,String> params = new HashMap<>();
                params.put("Content-Type","application/x-www-form-urlencoded");
                return params;
            }
        };
        m.requestQueue.add(joinRequest);
    }
}
