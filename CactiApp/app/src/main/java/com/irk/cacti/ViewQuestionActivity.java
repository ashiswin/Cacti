package com.irk.cacti;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.BaseAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;
import java.util.HashMap;
import java.util.Map;

import de.hdodenhof.circleimageview.CircleImageView;
import fr.tkeunebr.gravatar.Gravatar;

public class ViewQuestionActivity extends AppCompatActivity {
    MainApplication m;

    TextView txtTitle, txtDescription, txtDate, txtScore;
    ImageView imgResolved, imgProfilePic;
    EditText edtReply;
    Button btnSubmit;
    ListView lstReplies;

    ReplyAdapter adapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_view_question);

        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setBackgroundDrawable(getDrawable(R.drawable.red_toolbar));

        final int questionId = getIntent().getIntExtra("questionId", 0);

        m = (MainApplication) getApplicationContext();

        txtTitle = (TextView) findViewById(R.id.txtTitle);
        txtDescription = (TextView) findViewById(R.id.txtDescription);
        txtDate = (TextView) findViewById(R.id.txtDate);
        txtScore = (TextView) findViewById(R.id.txtScore);
        imgResolved = (ImageView) findViewById(R.id.imgResolved);
        imgProfilePic = (ImageView) findViewById(R.id.imgProfilePic);
        edtReply = (EditText) findViewById(R.id.edtReply);
        btnSubmit = (Button) findViewById(R.id.btnSubmit);
        lstReplies = (ListView) findViewById(R.id.lstReplies);

        loadQuestion(questionId);

        btnSubmit.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                final String description = edtReply.getText().toString();
                if(description.isEmpty()) {
                    edtReply.setError("Please enter your reply");
                    return;
                }

                StringRequest replyRequest = new StringRequest(Request.Method.POST, m.SERVER_HOST + "/scripts/Reply.php", new Response.Listener<String>() {
                    @Override
                    public void onResponse(String response) {
                        try {
                            JSONObject r = new JSONObject(response);
                            if(r.getBoolean("success")) {
                                edtReply.setText("");
                                loadQuestion(questionId);
                            }
                            else {
                                Toast.makeText(ViewQuestionActivity.this, r.getString("message"), Toast.LENGTH_LONG).show();
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
                        params.put("questionId", questionId + "");
                        params.put("description", description);

                        return params;
                    }

                    @Override
                    public Map<String, String> getHeaders() throws AuthFailureError {
                        Map<String,String> params = new HashMap<>();
                        params.put("Content-Type","application/x-www-form-urlencoded");
                        return params;
                    }
                };

                m.requestQueue.add(replyRequest);
            }
        });
    }

    public void loadQuestion(int questionId) {
        StringRequest questionRequest = new StringRequest(m.SERVER_HOST + "/scripts/GetQuestion.php?questionId=" + questionId, new Response.Listener<String>() {
            @Override
            public void onResponse(String response) {
                try {
                    JSONObject result = new JSONObject(response);

                    if(result.getBoolean("success")) {
                        JSONObject question = result.getJSONObject("question");
                        txtTitle.setText(question.getString("title"));
                        txtDescription.setText(question.getString("description"));
                        txtDate.setText("Created by " + question.getString("askerName") + " on " + question.getString("askDate"));
                        txtScore.setText(question.getInt("score") + "");
                        imgResolved.setImageResource((question.getInt("resolved") == 1) ? R.drawable.resolved : R.drawable.unresolved);
                        getSupportActionBar().setTitle(question.getString("subjectName"));

                        adapter = new ReplyAdapter(question.getJSONArray("replies"));
                        lstReplies.setAdapter(adapter);
                        final String gravatarURL = Gravatar.init().with(m.email).defaultImage(1).size(100).build();

                        new Thread(new Runnable() {
                            @Override
                            public void run() {
                                try {
                                    URL url = new URL(gravatarURL);
                                    final Bitmap bmp = BitmapFactory.decodeStream(url.openConnection().getInputStream());
                                    runOnUiThread(new Runnable() {
                                        @Override
                                        public void run() {
                                            imgProfilePic.setImageBitmap(bmp);
                                        }
                                    });
                                } catch (MalformedURLException e) {
                                    e.printStackTrace();
                                } catch (IOException e) {
                                    e.printStackTrace();
                                }
                            }
                        }).start();
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
        }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

            }
        });

        m.requestQueue.add(questionRequest);
    }
    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle item selection
        switch (item.getItemId()) {
            case android.R.id.home:
                finish();
            default:
                return super.onOptionsItemSelected(item);
        }
    }

    class ReplyAdapter extends BaseAdapter {
        JSONArray data;

        public ReplyAdapter(JSONArray data) {
            this.data = data;
        }

        @Override
        public int getCount() {
            return data.length();
        }

        @Override
        public Object getItem(int position) {
            try {
                return data.getJSONObject(position);
            } catch (JSONException e) {
                e.printStackTrace();

                return null;
            }
        }

        @Override
        public long getItemId(int position) {
            try {
                return data.getJSONObject(position).getInt("id");
            } catch (JSONException e) {
                e.printStackTrace();
                return 0;
            }
        }

        @Override
        public View getView(int position, View convertView, ViewGroup parent) {
            View itemView;

            if(convertView == null) {
                LayoutInflater inflater = (LayoutInflater) getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                itemView = inflater.inflate(R.layout.list_reply_item, parent, false);
            }
            else {
                itemView = convertView;
            }

            TextView txtReply = (TextView) itemView.findViewById(R.id.txtReply);
            TextView txtScore = (TextView) itemView.findViewById(R.id.txtScore);
            TextView txtName = (TextView) itemView.findViewById(R.id.txtName);
            final CircleImageView imgProfilePic = (CircleImageView) itemView.findViewById(R.id.imgProfilePic);

            JSONObject o = (JSONObject) getItem(position);

            try {
                txtReply.setText(o.getString("description"));
                txtScore.setText(o.getString("score"));
                txtName.setText(o.getString("replierName"));

                final String gravatarURL = Gravatar.init().with(o.getString("replierEmail")).defaultImage(1).size(100).build();

                new Thread(new Runnable() {
                    @Override
                    public void run() {
                        try {
                            URL url = new URL(gravatarURL);
                            final Bitmap bmp = BitmapFactory.decodeStream(url.openConnection().getInputStream());
                            runOnUiThread(new Runnable() {
                                @Override
                                public void run() {
                                    imgProfilePic.setImageBitmap(bmp);
                                }
                            });
                        } catch (MalformedURLException e) {
                            e.printStackTrace();
                        } catch (IOException e) {
                            e.printStackTrace();
                        }
                    }
                }).start();
            } catch (JSONException e) {
                e.printStackTrace();
            }
            return itemView;
        }
    }
}
